<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract base class for fragments.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
abstract class AbstractFragment implements FragmentInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getIdentifier()
    {
        throw new \RuntimeException('The concrete implementation of AbstractFragment must override getIdentifier().');
    }

    /**
     * {@inheritdoc}
     */
    public static function getCategory()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsConfiguration(ConfigurationInterface $configuration = null)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderStrategy($configuration = null)
    {
        return 'inline';
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderOptions($configuration = null)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParameters(ConfigurationInterface $configuration = null)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function convertRequestToConfiguration(Request $request)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function renderAction(ConfigurationInterface $configuration = null);
}
