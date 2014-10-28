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

use Contao\Bundle\CoreBundle\Autoload\Config;
use Contao\Bundle\CoreBundle\Autoload\ConfigResolver;

class ConfigResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $resolver = new ConfigResolver();

        $this->assertInstanceOf('Contao\Bundle\CoreBundle\Autoload\ConfigResolver', $resolver);
    }

    public function testAdd()
    {
        $resolver = new ConfigResolver();
        $config = new Config();

        $result = $resolver->add($config);

        $this->assertInstanceOf('Contao\Bundle\CoreBundle\Autoload\ConfigResolver', $result);
    }

    /**
     * @dataProvider getBundlesMapForEnvironmentProvider
     */
    public function testGetBundlesMapForEnvironment($env, $configs, $expectedResult)
    {
        $resolver = new ConfigResolver();

        foreach ($configs as $config) {
            $resolver->add($config);
        }

        $actualResult = $resolver->getBundlesMapForEnvironment($env);

        $this->assertSame($expectedResult, $actualResult);
    }

    public function getBundlesMapForEnvironmentProvider()
    {
        $dummyConfig = new Config();
        $dummyConfig->setName('dummyName');
        $dummyConfig->setClass('dummyClass');

        return [
            'Test dev environment with regular configs' => [
                'dev',
                [
                    $dummyConfig
                ],
                [
                    'dummyName' => 'dummyClass'
                ]
            ]
        ];
    }
}
 