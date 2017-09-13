<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\FragmentRegistry;

use Contao\ContentProxy;
use Contao\CoreBundle\DependencyInjection\Compiler\FragmentRegistryPass;
use Contao\CoreBundle\FragmentRegistry\FragmentRegistry;
use Contao\CoreBundle\Tests\TestCase;
use Contao\ModuleProxy;
use Contao\PageProxy;

/**
 * Class FragmentRegistryTest.
 *
 * @author Yanick Witschi
 */
class FragmentRegistryTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $registry = new FragmentRegistry();

        $this->assertInstanceOf('Contao\CoreBundle\FragmentRegistry\FragmentRegistry', $registry);
    }

    public function testBasicOptionsAreEnsured()
    {
        $this->expectException(\InvalidArgumentException::class);

        $registry = new FragmentRegistry();
        $registry->addFragment('foobar', new \stdClass(), ['nonsense' => 'test']);
    }

    public function testAddAndGetFragment()
    {
        $fragment = new \stdClass();
        $registry = new FragmentRegistry();
        $registry->addFragment('foobar', $fragment, [
            'tag' => 'test',
            'type' => 'test',
            'controller' => 'test',
        ]);

        $this->assertSame($fragment, $registry->getFragment('foobar'));
    }

    public function testGetOptions()
    {
        $options = [
            'tag' => 'test',
            'type' => 'test',
            'controller' => 'test',
            'whatever' => 'more',
        ];
        $registry = new FragmentRegistry();
        $registry->addFragment('foobar', new \stdClass(), $options);

        $this->assertSame($options, $registry->getOptions('foobar'));
    }

    public function testGetFragments()
    {
        $options = [
            'tag' => 'test',
            'type' => 'test',
            'controller' => 'test',
            'whatever' => 'more',
        ];
        $registry = new FragmentRegistry();
        $registry->addFragment('foobar', new \stdClass(), $options);

        $this->assertcount(1, $registry->getFragments());
        $this->assertCount(0, $registry->getFragments(function ($identifier, $fragment) {
            return false;
        }));
    }

    public function testMapNewFragmentsToLegacyArrays()
    {
        $registry = new FragmentRegistry();
        $registry->setFramework($this->mockContaoFramework());
        $registry->addFragment('page-type', new \stdClass(), [
            'tag' => FragmentRegistryPass::TAG_FRAGMENT_PAGE_TYPE,
            'type' => 'test',
            'controller' => 'test',
        ]);
        $registry->addFragment('frontend-module', new \stdClass(), [
            'tag' => FragmentRegistryPass::TAG_FRAGMENT_FRONTEND_MODULE,
            'type' => 'test',
            'controller' => 'test',
            'category' => 'navigationMod',
        ]);
        $registry->addFragment('content-element', new \stdClass(), [
            'tag' => FragmentRegistryPass::TAG_FRAGMENT_CONTENT_ELEMENT,
            'type' => 'test',
            'controller' => 'test',
            'category' => 'text',
        ]);

        $registry->mapNewFragmentsToLegacyArrays();

        $this->assertSame(PageProxy::class, $GLOBALS['TL_PTY']['test']);
        $this->assertSame(ModuleProxy::class, $GLOBALS['FE_MOD']['navigationMod']['test']);
        $this->assertSame(ContentProxy::class, $GLOBALS['TL_CTE']['text']['test']);
    }
}
