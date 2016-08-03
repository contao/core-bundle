<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the Contao insert tags that cannot be cached for ESI usage.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class NonCacheableInsertTagsController extends Controller
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * NonCacheableInsertTagsController constructor.
     *
     * @param $framework
     */
    public function __construct($framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param string $insertTag
     */
    public function renderAction($insertTag)
    {
        $this->framework->initialize();

        $result = $this->framework->createInstance('Contao\InsertTags')
                        ->replace($insertTag, false);

        $response = new Response($result);
        $response->setPrivate();

        return $response;
    }
}
