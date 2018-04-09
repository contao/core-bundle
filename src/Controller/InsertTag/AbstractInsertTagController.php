<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Controller\InsertTag;

use Contao\CoreBundle\Event\ApplyInsertTagFlagsEvent;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractInsertTagController extends Controller
{
    /**
     * Apply insert tag flags to response.
     *
     * @param array    $flags
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    protected function applyInsertTagFlags(array $flags, Request $request, Response $response)
    {
        $event = new ApplyInsertTagFlagsEvent($flags, $request, $response);
        $this->get('event_dispatcher')->dispatch(ContaoCoreEvents::APPLY_INSERT_TAG_FLAGS, $event);

        return $event->getResponse();
    }
}
