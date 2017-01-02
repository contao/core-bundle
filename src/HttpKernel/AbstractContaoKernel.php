<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\HttpKernel;

use Symfony\Component\HttpKernel\Kernel;

/**
 * Contao kernel.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
abstract class AbstractContaoKernel extends Kernel
{
    /**
     * Returns the Contao root dir.
     *
     * @return string
     */
    abstract public function getContaoRootDir();

    /**
     * {@inheritdoc}
     */
    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();
        $parameters['contao.root_dir'] = $this->getContaoRootDir();

        return $parameters;
    }
}
