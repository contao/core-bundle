<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Fragment\Reference;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Fragment\Reference\FragmentReference;
use Contao\CoreBundle\Tests\TestCase;

class FragmentReferenceTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $reference = new FragmentReference('');

        $this->assertInstanceOf('Contao\CoreBundle\Fragment\Reference\FragmentReference', $reference);
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Controller\ControllerReference', $reference);
    }

    public function testSetsDefaultScopeToFrontend(): void
    {
        $reference = new FragmentReference('');

        $this->assertSame(ContaoCoreBundle::SCOPE_FRONTEND, $reference->attributes['scope']);
    }

    public function testDoesNotOverrideScopeIfItIsSet(): void
    {
        $reference = new FragmentReference('', ['scope' => 'foobar']);

        $this->assertSame('foobar', $reference->attributes['scope']);
    }

    public function testCanSetAndGetScopes(): void
    {
        $reference = new FragmentReference('');

        $this->assertTrue($reference->isFrontend());
        $this->assertSame(ContaoCoreBundle::SCOPE_FRONTEND, $reference->attributes['scope']);

        $reference->setBackend();
        $this->assertTrue($reference->isBackend());
        $this->assertFalse($reference->isFrontend());
        $this->assertSame(ContaoCoreBundle::SCOPE_BACKEND, $reference->attributes['scope']);

        $reference->setFrontend();
        $this->assertTrue($reference->isFrontend());
        $this->assertFalse($reference->isBackend());
        $this->assertSame(ContaoCoreBundle::SCOPE_FRONTEND, $reference->attributes['scope']);
    }
}
