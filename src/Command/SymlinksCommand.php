<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Command;

use Contao\CoreBundle\Analyzer\HtaccessAnalyzer;
use Contao\CoreBundle\Util\SymlinkUtil;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Symlinks the public resources into the web directory.
 */
class SymlinksCommand extends AbstractLockedCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var array
     */
    private $rows = [];

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @var int
     */
    private $statusCode = 0;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('contao:symlinks')
            ->setDefinition([
                new InputArgument('target', InputArgument::OPTIONAL, 'The target directory', 'web'),
            ])
            ->setDescription('Symlinks the public resources into the web directory.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->rootDir = $this->getContainer()->getParameter('kernel.project_dir');
        $this->webDir = rtrim($input->getArgument('target'), '/');

        $this->generateSymlinks();

        if (!empty($this->rows)) {
            $this->io->newLine();
            $this->io->table(['', 'Symlink', 'Target / Error'], $this->rows);
        }

        return $this->statusCode;
    }

    /**
     * Generates the symlinks in the web directory.
     */
    private function generateSymlinks(): void
    {
        $fs = new Filesystem();
        $uploadPath = $this->getContainer()->getParameter('contao.upload_path');

        // Remove the base folders in the document root
        $fs->remove($this->rootDir.'/'.$this->webDir.'/'.$uploadPath);
        $fs->remove($this->rootDir.'/'.$this->webDir.'/system/modules');
        $fs->remove($this->rootDir.'/'.$this->webDir.'/vendor');

        $this->symlinkFiles($uploadPath);
        $this->symlinkModules();
        $this->symlinkThemes();

        // Symlink the assets and themes directory
        $this->symlink('assets', $this->webDir.'/assets');
        $this->symlink('system/themes', $this->webDir.'/system/themes');

        // Symlinks the logs directory
        $this->symlink($this->getRelativePath($this->getContainer()->getParameter('kernel.logs_dir')), 'system/logs');
    }

    /**
     * Creates the file symlinks.
     *
     * @param string $uploadPath
     */
    private function symlinkFiles(string $uploadPath): void
    {
        $this->createSymlinksFromFinder(
            $this->findIn($this->rootDir.'/'.$uploadPath)->files()->name('.public'),
            $uploadPath
        );
    }

    /**
     * Creates symlinks for the public module subfolders.
     */
    private function symlinkModules(): void
    {
        $filter = function (SplFileInfo $file): bool {
            return HtaccessAnalyzer::create($file)->grantsAccess();
        };

        $this->createSymlinksFromFinder(
            $this->findIn($this->rootDir.'/system/modules')->files()->filter($filter)->name('.htaccess'),
            'system/modules'
        );
    }

    /**
     * Creates the theme symlinks.
     */
    private function symlinkThemes(): void
    {
        /** @var SplFileInfo[] $themes */
        $themes = $this->getContainer()->get('contao.resource_finder')->findIn('themes')->depth(0)->directories();

        foreach ($themes as $theme) {
            $path = $this->getRelativePath($theme->getPathname());

            if (0 === strpos($path, 'system/modules/')) {
                continue;
            }

            $this->symlink($path, 'system/themes/'.basename($path));
        }
    }

    /**
     * Generates symlinks from a Finder object.
     *
     * @param Finder $finder
     * @param string $prepend
     */
    private function createSymlinksFromFinder(Finder $finder, string $prepend): void
    {
        $files = $this->filterNestedPaths($finder, $prepend);

        foreach ($files as $file) {
            $path = rtrim($prepend.'/'.$file->getRelativePath(), '/');
            $this->symlink($path, $this->webDir.'/'.$path);
        }
    }

    /**
     * Generates a symlink.
     *
     * The method will try to generate relative symlinks and fall back to generating
     * absolute symlinks if relative symlinks are not supported (see #208).
     *
     * @param string $target
     * @param string $link
     */
    private function symlink(string $target, string $link): void
    {
        $target = strtr($target, '\\', '/');
        $link = strtr($link, '\\', '/');

        try {
            SymlinkUtil::symlink($target, $link, $this->rootDir);

            $this->rows[] = [
                sprintf(
                    '<fg=green;options=bold>%s</>',
                    '\\' === DIRECTORY_SEPARATOR ? 'OK' : "\xE2\x9C\x94" // HEAVY CHECK MARK (U+2714)
                ),
                $link,
                $target,
            ];
        } catch (\Exception $e) {
            $this->statusCode = 1;

            $this->rows[] = [
                sprintf(
                    '<fg=red;options=bold>%s</>',
                    '\\' === DIRECTORY_SEPARATOR ? 'ERROR' : "\xE2\x9C\x98" // HEAVY BALLOT X (U+2718)
                ),
                $link,
                sprintf('<error>%s</error>', $e->getMessage()),
            ];
        }
    }

    /**
     * Returns a finder instance to find files in the given path.
     *
     * @param string $path
     *
     * @return Finder
     */
    private function findIn(string $path): Finder
    {
        return Finder::create()
            ->ignoreDotFiles(false)
            ->sort(
                function (SplFileInfo $a, SplFileInfo $b): int {
                    $countA = substr_count(strtr($a->getRelativePath(), '\\', '/'), '/');
                    $countB = substr_count(strtr($b->getRelativePath(), '\\', '/'), '/');

                    return $countA <=> $countB;
                }
            )
            ->followLinks()
            ->in($path)
        ;
    }

    /**
     * Filters nested paths so only the top folder is symlinked.
     *
     * @param Finder $finder
     * @param string $prepend
     *
     * @return SplFileInfo[]
     */
    private function filterNestedPaths(Finder $finder, string $prepend): array
    {
        $parents = [];
        $files = iterator_to_array($finder);

        /** @var SplFileInfo $file */
        foreach ($files as $key => $file) {
            $path = rtrim(strtr($prepend.'/'.$file->getRelativePath(), '\\', '/'), '/');

            if (!empty($parents)) {
                $parent = \dirname($path);

                while (false !== strpos($parent, '/')) {
                    if (\in_array($parent, $parents, true)) {
                        $this->rows[] = [
                            sprintf(
                                '<fg=yellow;options=bold>%s</>',
                                '\\' === DIRECTORY_SEPARATOR ? 'WARNING' : '!'
                            ),
                            $this->webDir.'/'.$path,
                            sprintf('<comment>Skipped because %s will be symlinked.</comment>', $parent),
                        ];

                        unset($files[$key]);
                        break;
                    }

                    $parent = \dirname($parent);
                }
            }

            $parents[] = $path;
        }

        return array_values($files);
    }

    /**
     * Returns the path relative to the root directory.
     *
     * @param string $path
     *
     * @return string
     */
    private function getRelativePath(string $path): string
    {
        return str_replace(strtr($this->rootDir, '\\', '/').'/', '', strtr($path, '\\', '/'));
    }
}
