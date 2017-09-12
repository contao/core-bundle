<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Translation;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Translation\Translator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Tests the TokenGenerator class.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class TranslatorTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $originalTranslator = $this->createMock(TranslatorInterface::class);
        $framework = $this->createMock(ContaoFrameworkInterface::class);
        $translator = new Translator($originalTranslator, $framework);

        $this->assertInstanceOf('Contao\CoreBundle\Translation\Translator', $translator);
        $this->assertInstanceOf('Symfony\Component\Translation\TranslatorInterface', $translator);
    }

    public function testForwardsMethodCalls()
    {
        $originalTranslator = $this->createMock(TranslatorInterface::class);

        $originalTranslator
            ->expects($this->once())
            ->method('trans')
            ->with('id', ['param' => 'value'], 'domain', 'en')
            ->willReturn('trans')
        ;

        $originalTranslator
            ->expects($this->once())
            ->method('transChoice')
            ->with('id', 3, ['param' => 'value'], 'domain', 'en')
            ->willReturn('transChoice')
        ;

        $originalTranslator
            ->expects($this->once())
            ->method('setLocale')
            ->with('en')
        ;

        $originalTranslator
            ->expects($this->once())
            ->method('getLocale')
            ->willReturn('en')
        ;

        $framework = $this->createMock(ContaoFrameworkInterface::class);

        $translator = new Translator($originalTranslator, $framework);

        $this->assertSame('trans', $translator->trans('id', ['param' => 'value'], 'domain', 'en'));
        $this->assertSame('transChoice', $translator->transChoice('id', 3, ['param' => 'value'], 'domain', 'en'));
        $translator->setLocale('en');
        $this->assertSame('en', $translator->getLocale());
    }

    public function testSkipsIfFrameworkIsNotInitialized()
    {
        $originalTranslator = $this->createMock(TranslatorInterface::class);
        $framework = $this->createMock(ContaoFrameworkInterface::class);

        $framework
            ->expects($this->any())
            ->method('isInitialized')
            ->willReturn(false)
        ;

        $translator = new Translator($originalTranslator, $framework);

        $GLOBALS['TL_LANG']['MSC']['foo'] = 'bar';

        $this->assertSame('MSC.foo', $translator->trans('MSC.foo', [], 'contao_default'));

        unset($GLOBALS['TL_LANG']['MSC']['foo']);
    }

    public function testReadsFromGlobals()
    {
        $originalTranslator = $this->createMock(TranslatorInterface::class);
        $framework = $this->createMock(ContaoFrameworkInterface::class);

        $framework
            ->expects($this->atLeastOnce())
            ->method('isInitialized')
            ->willReturn(true)
        ;

        $systemAdapter = $this->createMock(Adapter::class);

        $systemAdapter
            ->expects($this->atLeastOnce())
            ->method('__call')
            ->with('loadLanguageFile', ['default'])
        ;

        $framework
            ->expects($this->atLeastOnce())
            ->method('getAdapter')
            ->willReturn($systemAdapter)
        ;

        $translator = new Translator($originalTranslator, $framework);

        $this->assertSame('MSC.foo', $translator->trans('MSC.foo', [], 'contao_default'));

        $GLOBALS['TL_LANG']['MSC']['foo'] = 'bar';

        $this->assertSame('bar', $translator->trans('MSC.foo', [], 'contao_default'));

        $GLOBALS['TL_LANG']['MSC']['foo'] = 'bar %s baz %s';

        $this->assertSame('bar foo1 baz foo2', $translator->trans('MSC.foo', ['foo1', 'foo2'], 'contao_default'));

        $GLOBALS['TL_LANG']['MSC']['foo.bar\\baz'] = 'foo';

        $this->assertSame('foo', $translator->trans('MSC.foo\.bar\\\\baz', [], 'contao_default'));

        unset(
            $GLOBALS['TL_LANG']['MSC']['foo'],
            $GLOBALS['TL_LANG']['MSC']['foo.bar\\baz']
        );
    }

    public function testLoadsMessageDomainWithPrefix()
    {
        $originalTranslator = $this->createMock(TranslatorInterface::class);
        $framework = $this->createMock(ContaoFrameworkInterface::class);

        $framework
            ->expects($this->atLeastOnce())
            ->method('isInitialized')
            ->willReturn(true)
        ;

        $systemAdapter = $this->createMock(Adapter::class);

        $systemAdapter
            ->expects($this->atLeastOnce())
            ->method('__call')
            ->with('loadLanguageFile', ['tl_foobar'])
        ;

        $framework
            ->expects($this->atLeastOnce())
            ->method('getAdapter')
            ->willReturn($systemAdapter)
        ;

        $translator = new Translator($originalTranslator, $framework);

        $this->assertSame('foo', $translator->trans('foo', [], 'contao_tl_foobar'));

        $GLOBALS['TL_LANG']['tl_foobar']['foo'] = 'bar';

        $this->assertSame('bar', $translator->trans('foo', [], 'contao_tl_foobar'));

        unset($GLOBALS['TL_LANG']['tl_foobar']['foo']);
    }
}
