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

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InsertTagFlagEvent extends Event
{
    /**
     * @var string
     */
    private $insertTag;

    /**
     * @var string
     */
    private $parameters;

    /**
     * @var string
     */
    private $flag;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @param string   $insertTag
     * @param string   $parameters
     * @param string   $flag
     * @param Request  $request
     * @param Response $response
     */
    public function __construct(string $insertTag, string $parameters, string $flag, Request $request, Response $response)
    {
        $this->insertTag = $insertTag;
        $this->parameters = $parameters;
        $this->flag = $flag;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Returns the insert tag name (also called "type").
     * E.g. for something like {{trans::foobar:more|flag1|flag2}} this method
     * would return "trans".
     *
     * @return string
     */
    public function getInsertTag(): string
    {
        return $this->insertTag;
    }

    /**
     * Returns the insert tag parameters.
     * E.g. for something like {{trans::foobar:more|flag1|flag2}} this method
     * would return "foobar:more".
     *
     * @return string
     */
    public function getParameters(): string
    {
        return $this->parameters;
    }

    /**
     * Returns the insert tag flag.
     * E.g. for something like {{trans::foobar:more|flag1|flag2}} the event
     * would be called twice, both for "flag1" and "flag2" so this method
     * would return "flag1" the first time and "flag2" the
     * second time the event is dispatched.
     *
     * @return string
     */
    public function getFlag(): string
    {
        return $this->flag;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }
}
