<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Exception;

use Contao\CoreBundle\Exception\ResponseException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ResponseExceptionTest extends TestCase
{
    public function testSetsTheResponseStatusCodeAndContent(): void
    {
        $exception = new ResponseException(new Response('Hello world'));
        $response = $exception->getResponse();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello world', $response->getContent());
    }
}
