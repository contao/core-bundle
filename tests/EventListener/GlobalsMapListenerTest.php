<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\EventListener;

use Contao\CoreBundle\EventListener\GlobalsMapListener;
use Contao\CoreBundle\Tests\TestCase;

class GlobalsMapListenerTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $listener = new GlobalsMapListener([]);

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\GlobalsMapListener', $listener);
    }

    /**
     * @dataProvider valuesDataProvider
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param array $values
     * @param array $globals
     * @param array $expected
     */
    public function testMergesValuesIntoGlobals(array $globals, array $values, array $expected): void
    {
        $GLOBALS = $globals;

        $listener = new GlobalsMapListener($values);
        $listener->onInitializeSystem();

        $this->assertSame($expected, $GLOBALS);
    }

    public function valuesDataProvider(): array
    {
        return [
            [
                [],
                ['foo' => 'bar'],
                ['foo' => 'bar']
            ],
            [
                ['bar' => 'baz'],
                ['foo' => 'bar'],
                ['bar' => 'baz', 'foo' => 'bar'],
            ],
            [
                [],
                ['TL_CTE' => ['foo' => 'bar']],
                ['TL_CTE' => ['foo' => 'bar']],
            ],
            [
                ['TL_CTE' => ['bar' => 'baz']],
                ['TL_CTE' => ['foo' => 'bar']],
                ['TL_CTE' => ['bar' => 'baz', 'foo' => 'bar']],
            ],
            [
                ['TL_CTE' => ['foo' => 'bar']],
                ['TL_CTE' => ['foo' => 'baz']],
                ['TL_CTE' => ['foo' => 'baz']],
            ],
            [
                ['TL_CTE' => ['foo' => 'bar']],
                ['TL_CTE' => ['foo' => 'baz', 'bar' => 'baz']],
                ['TL_CTE' => ['foo' => 'baz', 'bar' => 'baz']],
            ],
        ];
    }
}
