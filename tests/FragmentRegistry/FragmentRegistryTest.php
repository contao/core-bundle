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

    public function testChecksTheBasicOptions()
    {
        $registry = new FragmentRegistry();

        $this->expectException(\InvalidArgumentException::class);

        $registry->addFragment('foobar', new \stdClass(), ['nonsense' => 'test']);
    }

    public function testReadsAndWritesFragments()
    {
        $fragment = new \stdClass();
        $registry = new FragmentRegistry();

        $registry->addFragment(
            'foobar',
            $fragment,
            [
                'tag' => 'test',
                'type' => 'test',
                'controller' => 'test',
            ]
        );

        $this->assertSame($fragment, $registry->getFragment('foobar'));
    }

    public function testReadsAndWritesOptions()
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

    public function testReturnsTheFragments()
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
    }

    public function testSupportsFilteringFragments()
    {
        $options = [
            'tag' => 'test',
            'type' => 'test',
            'controller' => 'test',
            'whatever' => 'more',
        ];

        $registry = new FragmentRegistry();
        $registry->addFragment('foobar', new \stdClass(), $options);

        $this->assertCount(0, $registry->getFragments(function () { return false; }));
    }
}
