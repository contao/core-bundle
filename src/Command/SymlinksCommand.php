<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Command;

use Contao\CoreBundle\Analyzer\HtaccessAnalyzer;
use Contao\CoreBundle\Util\SymlinkUtil;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Symlinks the public resources into the /web directory.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Yanick Witschi <https://github.com/toflar>
 */
class SymlinksCommand extends AbstractLockedCommand
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('contao:symlinks')
            ->setDescription('Symlinks the public resources into the /web directory.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));

        $this->generateSymlinks();

        return 0;
    }

    /**
     * Generates the symlinks in the web/ directory.
     */
    private function generateSymlinks()
    {
        $fs = new Filesystem();
        $uploadPath = $this->getContainer()->getParameter('contao.upload_path');

        // Remove the base folders in the document root
        $fs->remove($this->rootDir . '/web/' . $uploadPath);
        $fs->remove($this->rootDir . '/web/system/modules');
        $fs->remove($this->rootDir . '/web/vendor');

        $this->symlinkFiles($uploadPath);
        $this->symlinkModules();
        $this->symlinkThemes();

        // Symlink the assets and themes directory
        $this->symlink('assets', 'web/assets');
        $this->symlink('system/themes', 'web/system/themes');
        $this->symlink('app/logs', 'system/logs');
    }

    /**
     * Creates the file symlinks.
     *
     * @param string $uploadPath The upload path
     */
    private function symlinkFiles($uploadPath)
    {
        $this->createSymlinksFromFinder(
            $this->findIn($this->rootDir . '/' . $uploadPath)->files()->name('.public'),
            $uploadPath
        );
    }

    /**
     * Creates symlinks for the public module subfolders.
     */
    private function symlinkModules()
    {
        $filter = function (SplFileInfo $file) {
            return HtaccessAnalyzer::create($file)->grantsAccess();
        };

        $this->createSymlinksFromFinder(
            $this->findIn($this->rootDir . '/system/modules')->files()->filter($filter)->name('.htaccess'),
            'system/modules'
        );
    }

    /**
     * Creates the theme symlinks.
     */
    private function symlinkThemes()
    {
        /** @var SplFileInfo[] $themes */
        $themes = $this->getContainer()->get('contao.resource_finder')->findIn('themes')->depth(0)->directories();

        foreach ($themes as $theme) {
            $path = str_replace(strtr($this->rootDir, '\\', '/') . '/', '', strtr($theme->getPathname(), '\\', '/'));

            if (0 === strpos($path, 'system/modules/')) {
                continue;
            }

            $this->symlink($path, 'system/themes/' . basename($path));
        }
    }

    /**
     * Generates symlinks from a Finder object.
     *
     * @param Finder $finder  The finder object
     * @param string $prepend The path to prepend
     */
    private function createSymlinksFromFinder(Finder $finder, $prepend)
    {
        $filtered = $this->filterNestedPaths($finder);

        /** @var SplFileInfo $file */
        foreach ($filtered as $file) {
            $path = rtrim($prepend . '/' . $file->getRelativePath(), '/');
            $this->symlink($path, 'web/' . $path);
        }
    }

    /**
     * Generates a symlink.
     *
     * The method will try to generate relative symlinks and fall back to generating
     * absolute symlinks if relative symlinks are not supported (see #208).
     *
     * @param string $source The symlink name
     * @param string $target The symlink target
     */
    private function symlink($source, $target)
    {
        SymlinkUtil::symlink($source, $target, $this->rootDir);

        $this->output->writeln(
            sprintf(
                'Added <comment>%s</comment> as symlink to <comment>%s</comment>.',
                strtr($target, '\\', '/'),
                strtr($source, '\\', '/')
            )
        );
    }

    /**
     * Returns a finder instance to find files in the given path.
     *
     * @param string $path The path
     *
     * @return Finder The finder object
     */
    private function findIn($path)
    {
        return Finder::create()
            ->ignoreDotFiles(false)
            ->sort($this->getSortByPathDepthClosure())
            ->followLinks()
            ->in($path);
    }

    /**
     * Filter nested paths because if a parent is symlinked, all nested paths
     * will be symlinked automatically as well.
     *
     * @param Finder $finder
     *
     * @return array
     */
    private function filterNestedPaths(Finder $finder)
    {
        $parents = [];
        $result = iterator_to_array($finder);

        /** @var SplFileInfo $file */
        foreach ($result as $k => $file) {
            $chunks = explode('/', $file->getRelativePath());
            array_pop($chunks);

            $parent = implode('/', $chunks);

            if (in_array($parent, $parents)) {
                $this->output->writeln(
                    sprintf(
                        'Skipped <error>%s</error> because parent <error>%s</error> will be symlinked already.',
                        $file->getRelativePath(),
                        $parent
                    )
                );

                unset($result[$k]);
            }

            $parents[] = $file->getRelativePath();
        }

        return $result;
    }

    /**
     * Returns a closure to sort paths by depth.
     *
     * @return \Closure The closure
     */
    private function getSortByPathDepthClosure()
    {
        return function(SplFileInfo $a, SplFileInfo $b) {
            $countA = substr_count($a->getRelativePath(), '/');
            $countB = substr_count($b->getRelativePath(), '/');

            if ($countA === $countB) {
                return 0;
            }

            return ($countA < $countB) ? -1 : 1;
        };
    }
}
