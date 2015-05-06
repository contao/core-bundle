<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Command;

use Terminal42\ContaoAdapterBundle\Adapter\DbafsAdapter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Synchronizes the file system with the database.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Yanick Witschi <https://github.com/toflar>
 */
class FilesyncCommand extends LockedCommand
{
    /**
     * @var DbafsAdapter
     */
    private $dbafs;

    /**
     * Constructor.
     *
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     * @param DbafsAdapter $dbafs
     *
     * @throws \LogicException When the command name is empty
     *
     */
    public function __construct($name = null, DbafsAdapter $dbafs)
    {
        $this->dbafs = $dbafs;
        parent::__construct($name);
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
        $strLog = $this->dbafs->syncFiles();
        $output->writeln("Synchronization complete (see <info>$strLog</info>).");

        return 0;
    }
}
