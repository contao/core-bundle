<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Exception;

use Contao\CoreBundle\Exception\AjaxRedirectResponseException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the AjaxRedirectResponseException class.
 */
class AjaxRedirectResponseExceptionTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated(): void
    {
        $exception = new AjaxRedirectResponseException('http://example.org');

        $this->assertInstanceOf('Contao\CoreBundle\Exception\AjaxRedirectResponseException', $exception);
    }

    /**
     * Tests the getResponse() method.
     */
    public function testSetsTheResponseStatusCodeAndAjaxLocation(): void
    {
        $exception = new AjaxRedirectResponseException('http://example.org');

        $response = $exception->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://example.org', $response->headers->get('X-Ajax-Location'));
    }
}
