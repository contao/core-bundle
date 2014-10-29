<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\Test\Config\Loader;

use Contao\Config;
use Contao\CoreBundle\Config\Loader\ContaoLoader;

/**
 * Tests the Contao loader
 *
 * @author Leo Feyer <https://contao.org>
 */
class ContaoLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->config = $this->getMock('Contao\Config', null, [], '', false);
    }

    /**
     * Test with URL suffix and without language
     */
    public function testLoadWithoutLanguage()
    {
        $this->config->set('urlSuffix', '.html');
        $this->config->set('addLanguageToUrl', false);

        $loader     = new ContaoLoader($this->config);
        $collection = $loader->load(null, null);

        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $collection);

        $routes = $collection->all();

        // contao_default
        $this->assertArrayHasKey('contao_default', $routes);
        $this->assertEquals('/{alias}.{_format}', $routes['contao_default']->getPath());
        $this->assertEquals('ContaoCoreBundle:Frontend:index', $routes['contao_default']->getDefault('_controller'));
        $this->assertEquals('html', $routes['contao_default']->getDefault('_format'));
        $this->assertEquals('.*', $routes['contao_default']->getRequirement('alias'));
        $this->assertEquals('html', $routes['contao_default']->getRequirement('_format'));

        // contao_root
        $this->assertArrayHasKey('contao_root', $routes);
        $this->assertEquals('/', $routes['contao_root']->getPath());
        $this->assertEquals('ContaoCoreBundle:Frontend:index', $routes['contao_root']->getDefault('_controller'));
        $this->assertEquals('html', $routes['contao_root']->getDefault('_format'));
    }

    /**
     * Test with URL suffix and with language
     */
    public function testLoadWitLanguage()
    {
        $this->config->set('urlSuffix', '.html');
        $this->config->set('addLanguageToUrl', true);

        $loader     = new ContaoLoader($this->config);
        $collection = $loader->load(null, null);

        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $collection);

        $routes = $collection->all();

        // contao_locale
        $this->assertArrayHasKey('contao_locale', $routes);
        $this->assertEquals('/{_locale}/{alias}.{_format}', $routes['contao_locale']->getPath());
        $this->assertEquals('ContaoCoreBundle:Frontend:index', $routes['contao_locale']->getDefault('_controller'));
        $this->assertEquals('html', $routes['contao_locale']->getDefault('_format'));
        $this->assertEquals('.*', $routes['contao_locale']->getRequirement('alias'));
        $this->assertEquals('html', $routes['contao_locale']->getRequirement('_format'));
        $this->assertEquals('[a-z]{2}(\-[A-Z]{2})?', $routes['contao_locale']->getRequirement('_locale'));

        // contao_default
        $this->assertArrayHasKey('contao_default', $routes);
        $this->assertEquals('/{alias}.{_format}', $routes['contao_default']->getPath());
        $this->assertEquals('ContaoCoreBundle:Frontend:index', $routes['contao_default']->getDefault('_controller'));
        $this->assertEquals('html', $routes['contao_default']->getDefault('_format'));
        $this->assertEquals('.*', $routes['contao_default']->getRequirement('alias'));
        $this->assertEquals('html', $routes['contao_default']->getRequirement('_format'));
        $this->assertEquals('[a-z]{2}(\-[A-Z]{2})?', $routes['contao_default']->getRequirement('_locale'));

        // contao_root
        $this->assertArrayHasKey('contao_root', $routes);
        $this->assertEquals('/', $routes['contao_root']->getPath());
        $this->assertEquals('ContaoCoreBundle:Frontend:index', $routes['contao_root']->getDefault('_controller'));
        $this->assertEquals('html', $routes['contao_root']->getDefault('_format'));
    }

    /**
     * Test without URL suffix and without language
     */
    public function testLoadWithoutLanguageAndWithoutSuffix()
    {
        $this->config->set('urlSuffix', '');
        $this->config->set('addLanguageToUrl', false);

        $loader     = new ContaoLoader($this->config);
        $collection = $loader->load(null, null);

        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $collection);

        $routes = $collection->all();

        // contao_default
        $this->assertArrayHasKey('contao_default', $routes);
        $this->assertEquals('/{alias}', $routes['contao_default']->getPath());
        $this->assertEquals('ContaoCoreBundle:Frontend:index', $routes['contao_default']->getDefault('_controller'));
        $this->assertEquals('', $routes['contao_default']->getDefault('_format'));
        $this->assertEquals('.*', $routes['contao_default']->getRequirement('alias'));
        $this->assertEquals('', $routes['contao_default']->getRequirement('_format'));

        // contao_root
        $this->assertArrayHasKey('contao_root', $routes);
        $this->assertEquals('/', $routes['contao_root']->getPath());
        $this->assertEquals('ContaoCoreBundle:Frontend:index', $routes['contao_root']->getDefault('_controller'));
        $this->assertEquals('', $routes['contao_root']->getDefault('_format'));
    }

    /**
     * Test without URL suffix and with language
     */
    public function testLoadWithLanguageAndWithoutSuffix()
    {
        $this->config->set('urlSuffix', '');
        $this->config->set('addLanguageToUrl', true);

        $loader     = new ContaoLoader($this->config);
        $collection = $loader->load(null, null);

        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $collection);

        $routes = $collection->all();

        // contao_locale
        $this->assertArrayHasKey('contao_locale', $routes);
        $this->assertEquals('/{_locale}/{alias}', $routes['contao_locale']->getPath());
        $this->assertEquals('ContaoCoreBundle:Frontend:index', $routes['contao_locale']->getDefault('_controller'));
        $this->assertEquals('', $routes['contao_locale']->getDefault('_format'));
        $this->assertEquals('.*', $routes['contao_locale']->getRequirement('alias'));
        $this->assertEquals('', $routes['contao_locale']->getRequirement('_format'));
        $this->assertEquals('[a-z]{2}(\-[A-Z]{2})?', $routes['contao_locale']->getRequirement('_locale'));

        // contao_default
        $this->assertArrayHasKey('contao_default', $routes);
        $this->assertEquals('/{alias}', $routes['contao_default']->getPath());
        $this->assertEquals('ContaoCoreBundle:Frontend:index', $routes['contao_default']->getDefault('_controller'));
        $this->assertEquals('', $routes['contao_default']->getDefault('_format'));
        $this->assertEquals('.*', $routes['contao_default']->getRequirement('alias'));
        $this->assertEquals('', $routes['contao_default']->getRequirement('_format'));
        $this->assertEquals('[a-z]{2}(\-[A-Z]{2})?', $routes['contao_default']->getRequirement('_locale'));

        // contao_root
        $this->assertArrayHasKey('contao_root', $routes);
        $this->assertEquals('/', $routes['contao_root']->getPath());
        $this->assertEquals('ContaoCoreBundle:Frontend:index', $routes['contao_root']->getDefault('_controller'));
        $this->assertEquals('', $routes['contao_root']->getDefault('_format'));
    }

    /**
     * Ensure that the loader supports "contao_frontend"
     */
    public function testSupportsContaoFrontend()
    {
        $loader = new ContaoLoader($this->config);

        $this->assertTrue($loader->supports(null, 'contao_frontend'));
    }
}
