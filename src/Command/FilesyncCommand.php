<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Command;

use Contao\Dbafs;
use Contao\CoreBundle\ContaoFramework;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Synchronizes the file system with the database.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class FilesyncCommand extends AbstractLockedCommand
{
    /**
     * @var ContaoFramework
    */
    private $framework;

    /**
     * Constructor.
     *
     * @param ContaoFramework $framework The Contao framework service
     */
     public function __construct(ContaoFramework $framework)
     {
         $this->framework = $framework;
     }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('contao:filesync')
            ->setDescription('Synchronizes the file system with the database.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output)
    {
        $this->framework->initialize();

        $strLog = Dbafs::syncFiles();
        $output->writeln('Synchronization complete (see <info>' . $strLog . '</info>).');

        return 0;
    }
}
