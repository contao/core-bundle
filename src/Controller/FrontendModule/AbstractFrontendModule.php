<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FragmentRegistry\AbstractFragment;

/**
 * Abstract base class for front end modules.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
abstract class AbstractFrontendModule extends AbstractFragment
{
    /**
     * {@inheritdoc}
     */
    protected function getControllerAttributes(array $configuration)
    {
        return array_merge(
            parent::getControllerAttributes($configuration), [
                'moduleModel' => $configuration['moduleModel']->id,
                'inColumn' => $configuration['inColumn'],
            ]
        );
    }
}
