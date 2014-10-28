<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\Test\Autoload;

use Contao\Bundle\CoreBundle\Autoload\IniParser;
use Symfony\Component\Finder\SplFileInfo;

class IniParserTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $parser = new IniParser();

        $this->assertInstanceOf('Contao\Bundle\CoreBundle\Autoload\IniParser', $parser);
        $this->assertInstanceOf('Contao\Bundle\CoreBundle\Autoload\ParserInterface', $parser);
    }

    public function testParse()
    {
        $parser = new IniParser();
        $file = new SplFileInfo('dummy', 'relativePath', 'relativePathName');

        $this->assertSame([
            'bundles' => [[
                'class'         => null,
                'name'          => 'dummy',
                'replace'       => [],
                'environments'  => ['all'],
                'load-after'    => []
            ]]
        ], $parser->parse($file));
    }
}
 