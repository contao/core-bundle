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

use Contao\CoreBundle\Event\InsertTagFlagEvent;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractInsertTagController extends Controller
{

    /**
     * Default action for any insert tag.
     *
     * @param Request $request
     * @param string  $parameters
     * @param array   $flags
     *
     * @return Response
     */
    public function __invoke(Request $request, string $insertTag, string $parameters, array $flags): Response
    {
        $response = $this->getResponse($request, $parameters, $flags);

        $this->applyInsertTagFlags($insertTag, $parameters, $flags, $request, $response);

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $parameters
     * @param array   $flags
     *
     * @return Response
     */
    abstract protected function getResponse(Request $request, string $parameters, array $flags): Response;

    /**
     * Apply insert tag flags to response.
     *
     * @param string   $insertTag
     * @param string   $parameters
     * @param array    $flags
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    protected function applyInsertTagFlags(string $insertTag, $parameters, array $flags, Request $request, Response $response): Response
    {
        $eventDispatcher = $this->get('event_dispatcher');

        foreach ($flags as $flag) {
            $event = new InsertTagFlagEvent($insertTag, $parameters, $flag, $request, $response);
            $eventDispatcher->dispatch(ContaoCoreEvents::INSERT_TAG_FLAG, $event);

            $response = $event->getResponse();
        }

        return $response;
    }
}
