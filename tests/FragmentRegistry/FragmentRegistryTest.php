<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\FragmentRegistry;

use Contao\CoreBundle\FragmentRegistry\FragmentRegistry;
use Contao\CoreBundle\Tests\TestCase;

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
}
