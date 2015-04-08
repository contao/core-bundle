<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Cache;

use Contao\CoreBundle\Config\Dumper\CombinedFileDumper;
use Contao\CoreBundle\Config\Loader\PhpFileLoader;
use Contao\CoreBundle\Config\Loader\XliffFileLoader;
use Contao\CoreBundle\Finder\ResourceFinder;
use Contao\DcaExtractor;
use Contao\Environment;
use Contao\PageModel;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Generates the Contao cache during cache warmup.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ContaoCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ResourceFinder
     */
    private $finder;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * Constructor.
     *
     * @param Filesystem     $filesystem The filesystem object
     * @param ResourceFinder $finder     The resource finder object
     * @param string         $rootDir    The root directory
     * @param Connection     $connection The Doctrine connection
     *
     * @internal param Registry $doctrine The doctrine registry
     */
    public function __construct(Filesystem $filesystem, ResourceFinder $finder, $rootDir, Connection $connection)
    {
        $this->filesystem = $filesystem;
        $this->finder     = $finder;
        $this->rootDir    = dirname($rootDir);
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->generateConfigCache($cacheDir);
        $this->generateCacheMapper($cacheDir);
        $this->generateDcaCache($cacheDir);
        $this->generateLanguageCache($cacheDir);
        $this->generateDcaExtracts($cacheDir);
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * Generates the config cache.
     *
     * @param string $cacheDir The cache directory
     */
    private function generateConfigCache($cacheDir)
    {
        $dumper = new CombinedFileDumper(
            $this->filesystem,
            new PhpFileLoader(),
            "$cacheDir/contao"
        );

        $files = $this->finder->in('config')->files()->name('autoload.php');
        $dumper->dump(iterator_to_array($files), 'config/autoload.php');

        $files = $this->finder->in('config')->files()->name('config.php');
        $dumper->dump(iterator_to_array($files), 'config/config.php');
    }

    /**
     * Generates the cache mapper array.
     *
     * @param string $cacheDir The cache directory
     */
    private function generateCacheMapper($cacheDir)
    {
        $mapper = [];
        $pages  = PageModel::findPublishedRootPages();

        if (null === $pages) {
            return;
        }

        while ($pages->next()) {
            $base = '*' . Environment::get('path') . '/';

            if ('' !== $pages->dns) {
                $base = ($pages->useSSL ? 'https://' : 'http://') . $pages->dns . Environment::get('path') . '/';
            }

            if ($pages->fallback) {
                $mapper[$base . 'empty.fallback'] = $base . 'empty.' . $pages->language;
            }

            $mapper[$base . 'empty.' . $pages->language] = $base . 'empty.' . $pages->language;
        }

        $this->filesystem->dumpFile(
            "$cacheDir/contao/config/mapping.php",
            sprintf("<?php\n\nreturn %s;\n", var_export($mapper, true))
        );
    }

    /**
     * Generates the DCA cache.
     *
     * @param string $cacheDir The cache directory
     */
    private function generateDcaCache($cacheDir)
    {
        $dumper = new CombinedFileDumper(
            $this->filesystem,
            new PhpFileLoader(),
            "$cacheDir/contao"
        );

        $processed = array();

        /** @var SplFileInfo[] $files */
        $files = $this->finder->in('dca')->files()->name('*.php');

        foreach ($files as $file) {
            if (in_array($file->getBasename(), $processed)) {
                continue;
            }

            $processed[] = $file->getBasename();

            $subfiles = $this->finder->in('dca')->files()->name($file->getBasename());
            $dumper->dump(iterator_to_array($subfiles), 'dca/' . $file->getBasename());
        }
    }

    /**
     * Generates the language cache.
     *
     * @param string $cacheDir The cache directory
     */
    private function generateLanguageCache($cacheDir)
    {
        $dumper = new CombinedFileDumper(
            $this->filesystem,
            new DelegatingLoader(new LoaderResolver([new PhpFileLoader(), new XliffFileLoader($this->rootDir)])),
            "$cacheDir/contao"
        );

        $dumper->setHeader("<?php\n");

        foreach ($this->getLanguagesInUse() as $language) {
            $processed = [];

            try {
                $files = $this->finder->in("languages/$language")->files()->name('/\.(php|xlf)$/');
            } catch (\InvalidArgumentException $e) {
                continue; // the language does not exist
            }

            /** @var SplFileInfo[] $files */
            foreach ($files as $file) {
                $name = substr($file->getBasename(), 0, -4);

                if (in_array($name, $processed)) {
                    continue;
                }

                $processed[] = $name;

                $subfiles = $this->finder->in("languages/$language")->files()->name("/^$name\\.(php|xlf)$/");
                $dumper->dump(iterator_to_array($subfiles), "languages/$language/$name.php", ['type' => $language]);
            }
        }
    }

    /**
     * Generates the DCA extracts.
     *
     * @param string $cacheDir The cache directory
     */
    private function generateDcaExtracts($cacheDir)
    {
        $processed = array();

        /** @var SplFileInfo[] $files */
        $files = $this->finder->in('dca')->files()->name('*.php');

        foreach ($files as $file) {
            if (in_array($file->getBasename(), $processed)) {
                continue;
            }

            $processed[] = $file->getBasename();

            $table   = $file->getBasename('.php');
            $extract = DcaExtractor::getInstance($table);

            if (!$extract->isDbTable()) {
                continue;
            }

            $this->filesystem->dumpFile(
                "$cacheDir/contao/sql/$table.php",
                "<?php\n\n"
                    . sprintf("\$this->arrMeta = %s;\n\n", var_export($extract->getMeta(), true))
                    . sprintf("\$this->arrFields = %s;\n\n", var_export($extract->getFields(), true))
                    . sprintf("\$this->arrOrderFields = %s;\n\n", var_export($extract->getOrderFields(), true))
                    . sprintf("\$this->arrKeys = %s;\n\n", var_export($extract->getKeys(), true))
                    . sprintf("\$this->arrRelations = %s;\n\n", var_export($extract->getRelations(), true))
                    . "\$this->blnIsDbTable = true;\n"
            );
        }
    }

    /**
     * Returns the languages which are currently in use.
     *
     * @return array The languages array
     */
    private function getLanguagesInUse()
    {
        // Get all languages in use (see #6013)
        $statement = $this->connection->query("
            SELECT language FROM tl_member
            UNION SELECT language FROM tl_user
            UNION SELECT REPLACE(language, '-', '_') FROM tl_page
            WHERE type='root'
        ");

        $languages = [];

        while ($language = $statement->fetch(\PDO::FETCH_OBJ)) {
            if ('' === $language->language) {
                continue;
            }

            $languages[] = $language->language;

            // Also cache "de" if "de-CH" is requested
            if (strlen($language->language) > 2) {
                $languages[] = substr($language->language, 0, 2);
            }
        }

        return array_unique($languages);
    }
}
