<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Session\Attribute;

use Contao\CoreBundle\Session\Attribute\ArrayAttributeBag;
use Contao\CoreBundle\Session\LazySessionAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class LazySessionAccessTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $accessor = new LazySessionAccess($session);

        $this->assertInstanceOf('Contao\CoreBundle\Session\LazySessionAccess', $accessor);
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation Accessing $_SESSION directly is deprecated and support will be dropped with Contao 5.0. Use the Symfony request instead to work with the session.
     */
    public function testStartsSessionOnAccess(): void
    {
        $beBag = new AttributeBag();
        $beBag->setName('contao_backend');
        $feBag = new AttributeBag();
        $feBag->setName('contao_frontend');

        $session = new Session(new MockArraySessionStorage());
        $session->registerBag($beBag);
        $session->registerBag($feBag);
        $accessor = new LazySessionAccess($session);

        // Do not use $_SESSION here for maximum compat
        $FOOBAR = $accessor;

        $this->assertFalse($session->isStarted());

        // In reality would be $_SESSION['foobar'] = 'test';
        $FOOBAR['foobar'] = 'test';

        $this->assertTrue($session->isStarted());

        $this->assertSame('test', $session->get('foobar'));
    }
}
