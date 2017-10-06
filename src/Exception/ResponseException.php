<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

class ResponseException extends \RuntimeException
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @param Response        $response
     * @param \Exception|null $previous
     */
    public function __construct(Response $response, \Exception $previous = null)
    {
        if (!$response->headers->has('X-Status-Code')) {
            $response->headers->set('X-Status-Code', $response->getStatusCode());
        }

        $this->response = $response;

        parent::__construct('This exception has no message. Use $exception->getResponse() instead.', 0, $previous);
    }

    /**
     * Returns the response object.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
