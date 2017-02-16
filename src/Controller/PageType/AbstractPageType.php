<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\PageType;

use Contao\CoreBundle\Controller\FragmentRegistry\FragmentType\PageTypeInterface;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract class for shared logic for page types.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
abstract class AbstractPageType implements PageTypeInterface
{
    /**
     * {@inheritdoc}
     */
    abstract public function getName();

    /**
     * {@inheritdoc}
     */
    abstract public function renderAction(Request $request);

    /**
     * {@inheritdoc}
     */
    public function getRenderStrategy(array $configuration)
    {
        return 'inline';
    }

    /**
     * Gets the render options for the render strategy. Most of the times
     * this is an empty array. Some strategies don't even support options but
     * some (e.g. like ESI) do to add e.g. comments to the <esi> tag.
     * The passed configuration array contains whatever the triggering code
     * wants to pass on to your fragment.
     * See FragmentRegistryInterface::renderFragment()
     *
     * @param array $configuration
     *
     * @return array
     */
    public function getRenderOptions(array $configuration)
    {
        return [];
    }

    /**
     * Your fragment likely needs some request query parameters if you use any
     * other render strategy than "inline". Return them here as key->value and
     * you will receive them as query parameters in the renderAction() method.
     * The passed configuration array contains whatever the triggering code
     * wants to pass on to your fragment.
     * See FragmentRegistryInterface::renderFragment()
     *
     * @param array $configuration
     *
     * @return array
     */
    public function getQueryParameters(array $configuration)
    {
        $params = [];

        if (isset($configuration['pageModel'])
            && $configuration['pageModel'] instanceof PageModel
        ) {
            $params['pageId'] = $configuration['pageModel']->id;
        }

        return $params;
    }
}
