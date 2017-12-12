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

use PackageVersions\Versions;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated Deprecated since Contao 4.4, to be removed in Contao 5.0; use
 *             "composer show | grep contao/core-bundle | awk '{ print $2 }'" instead
 */
class VersionCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('contao:version')
            ->setDescription('Outputs the contao/core-bundle version (deprecated).')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $version = strstr(Versions::getVersion('contao/core-bundle'), '@', true);

        $output->writeln($version);

        return 0;
    }
}
