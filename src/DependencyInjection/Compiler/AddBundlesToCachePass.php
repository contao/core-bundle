<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\DependencyInjection\Compiler;

use Contao\Bundle\CoreBundle\HttpKernel\ContaoKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Generates the bundles cache
 *
 * @author Leo Feyer <https://contao.org>
 */
class AddBundlesToCachePass implements CompilerPassInterface
{
    /**
     * @var ContaoKernel
     */
    private $kernel;

    /**
     * Store the kernel
     *
     * @param ContaoKernel $kernel The kernel object
     */
    public function __construct(ContaoKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->kernel->writeBundleCache();
    }
}
