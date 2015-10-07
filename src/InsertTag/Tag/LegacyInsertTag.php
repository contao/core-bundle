<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\InsertTag\Tag;

use Contao\CoreBundle\InsertTag\InsertTagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * An InsertTag that handles all the legacy InsertTags.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class LegacyInsertTag implements InsertTagInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'legacy';
    }

    /**
     * {@inheritdoc}
     */
    public function isResponsible($insertTag, Request $request)
    {
        // Always return true because our legacy support has to go through all
        // classes using the hook and should thus always have the lowest
        // priority
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse($insertTag, Request $request)
    {
        $response = new Response();

        // TODO: Here we'll replace all the

        return $response;
    }
}
