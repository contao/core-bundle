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

use Contao\Bundle\CoreBundle\Autoload\JsonParser;
use Symfony\Component\Finder\SplFileInfo;

class JsonParserTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $parser = new JsonParser();

        $this->assertInstanceOf('Contao\Bundle\CoreBundle\Autoload\JsonParser', $parser);
        $this->assertInstanceOf('Contao\Bundle\CoreBundle\Autoload\ParserInterface', $parser);
    }

    public function testParse()
    {
        $parser = new JsonParser();
        $normalized = [
            'bundles' => [
                'Contao\Bundle\CoreBundle\ContaoCoreBundle' => [
                  'class'         => 'Contao\Bundle\CoreBundle\ContaoCoreBundle',
                  'name'          => 'ContaoCoreBundle',
                  'replace'       => [],
                  'environments'  => ['all'],
                  'load-after'    => []
              ]]
        ];

        $fileMock = $this->getMockBuilder('\Symfony\Component\Finder\SplFileInfo')
            ->setConstructorArgs([
                'dummy', 'relativePath', 'relativePathName'
            ])
            ->getMock();

        $fileMock->expects($this->once())
            ->method('isFile')
            ->will($this->returnValue(true));

        $fileMock->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue(json_encode($normalized, true)));


        $this->assertSame($normalized, $parser->parse($fileMock));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWillThrowExceptionIfFileNotExists()
    {
        $parser = new JsonParser();
        $file = new SplFileInfo('iDoNotExist', 'relativePath', 'relativePathName');

        $parser->parse($file);
    }
}
 