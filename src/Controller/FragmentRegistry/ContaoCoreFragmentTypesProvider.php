<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

use Contao\CoreBundle\Controller\PageType\PageTypeInterface;

/**
 * Provides the contao core fragment types
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class ContaoCoreFragmentTypesProvider implements FragmentTypesProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFragmentTypes()
    {
        return [
            PageTypeInterface::class => PageTypeInterface::TAG_NAME,
        ];
    }
}
