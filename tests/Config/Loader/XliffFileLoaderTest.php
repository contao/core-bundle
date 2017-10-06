<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Config\Loader;

use Contao\CoreBundle\Config\Loader\XliffFileLoader;
use Contao\CoreBundle\Tests\TestCase;

class XliffFileLoaderTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(
            'Contao\CoreBundle\Config\Loader\XliffFileLoader',
            new XliffFileLoader($this->getRootDir().'/app')
        );
    }

    public function testSupportsXlfFiles(): void
    {
        $loader = new XliffFileLoader($this->getRootDir().'/app');

        $this->assertTrue(
            $loader->supports(
                $this->getRootDir().'/vendor/contao/test-bundle/Resources/contao/languages/en/default.xlf'
            )
        );

        $this->assertFalse(
            $loader->supports(
                $this->getRootDir().'/vendor/contao/test-bundle/Resources/contao/languages/en/tl_test.php'
            )
        );
    }

    public function testLoadsXlfFilesIntoAString(): void
    {
        $loader = new XliffFileLoader($this->getRootDir(), false);

        $source = <<<'TXT'

// vendor/contao/test-bundle/Resources/contao/languages/en/default.xlf
$GLOBALS['TL_LANG']['MSC']['first'] = 'This is the first source';
$GLOBALS['TL_LANG']['MSC']['second'][0] = 'This is the second source';
$GLOBALS['TL_LANG']['MSC']['third']['with'][1] = 'This is the third source';
$GLOBALS['TL_LANG']['tl_layout']['responsive.css'][1] = 'This is the fourth source';
$GLOBALS['TL_LANG']['MSC']['fifth'] = "This is the\nfifth source";
$GLOBALS['TL_LANG']['MSC']['only_source'] = 'This is the source';
$GLOBALS['TL_LANG']['MSC']['in_group_1'] = 'This is in group 1 source';
$GLOBALS['TL_LANG']['MSC']['in_group_2'] = 'This is in group 2 source';
$GLOBALS['TL_LANG']['MSC']['second_file'] = 'This is the target';

TXT;

        $target = <<<'TXT'

// vendor/contao/test-bundle/Resources/contao/languages/en/default.xlf
$GLOBALS['TL_LANG']['MSC']['first'] = 'This is the first target';
$GLOBALS['TL_LANG']['MSC']['second'][0] = 'This is the second target';
$GLOBALS['TL_LANG']['MSC']['third']['with'][1] = 'This is the third target';
$GLOBALS['TL_LANG']['tl_layout']['responsive.css'][1] = 'This is the fourth target';
$GLOBALS['TL_LANG']['MSC']['fifth'] = "This is the\nfifth target";
$GLOBALS['TL_LANG']['MSC']['only_target'] = 'This is the target';
$GLOBALS['TL_LANG']['MSC']['in_group_1'] = 'This is in group 1 target';
$GLOBALS['TL_LANG']['MSC']['in_group_2'] = 'This is in group 2 target';
$GLOBALS['TL_LANG']['MSC']['second_file'] = 'This is the source';

TXT;

        $this->assertSame(
            $source,
            $loader->load(
                $this->getRootDir().'/vendor/contao/test-bundle/Resources/contao/languages/en/default.xlf',
                'en'
            )
        );

        $this->assertSame(
            $target,
            $loader->load(
                $this->getRootDir().'/vendor/contao/test-bundle/Resources/contao/languages/en/default.xlf',
                'de'
            )
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadsXlfFilesIntoTheGlobalVariables(): void
    {
        $loader = new XliffFileLoader($this->getRootDir().'/app', true);

        $loader->load(
            $this->getRootDir().'/vendor/contao/test-bundle/Resources/contao/languages/en/default.xlf',
            'en'
        );

        $this->assertSame('This is the first source', $GLOBALS['TL_LANG']['MSC']['first']);
        $this->assertSame('This is the second source', $GLOBALS['TL_LANG']['MSC']['second'][0]);
        $this->assertSame('This is the third source', $GLOBALS['TL_LANG']['MSC']['third']['with'][1]);

        $loader->load(
            $this->getRootDir().'/vendor/contao/test-bundle/Resources/contao/languages/en/default.xlf',
            'de'
        );

        $this->assertSame('This is the first target', $GLOBALS['TL_LANG']['MSC']['first']);
        $this->assertSame('This is the second target', $GLOBALS['TL_LANG']['MSC']['second'][0]);
        $this->assertSame('This is the third target', $GLOBALS['TL_LANG']['MSC']['third']['with'][1]);
    }

    public function testFailsIfThereAreTooManyNestingLevels(): void
    {
        $loader = new XliffFileLoader($this->getRootDir().'/app', false);

        $this->expectException('OutOfBoundsException');

        $loader->load(
            $this->getRootDir().'/vendor/contao/test-bundle/Resources/contao/languages/en/error.xlf',
            'en'
        );
    }
}
