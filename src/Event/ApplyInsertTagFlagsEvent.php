<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class ApplyInsertTagFlagsEvent extends Event
{
    /**
     * @var array
     */
    private $flags;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * ApplyInsertTagFlagsEvent constructor.
     *
     * @param array    $flags
     * @param Request  $request
     * @param Response $response
     */
    public function __construct(array $flags, Request $request, Response $response)
    {
        $this->flags = $flags;
        $this->request = $request;
        $this->response = $response;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
