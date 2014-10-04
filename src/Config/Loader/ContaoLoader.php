<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\Config\Loader;

use Contao\Config;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Dynamically adds routes
 *
 * @author Andreas Schempp <http://terminal42.ch>
 * @author Leo Feyer <https://contao.org>
 */
class ContaoLoader extends Loader
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param Config $config The Contao configuration object
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $addlang = $this->config->get('addLanguageToUrl');
        $suffix  = substr($this->config->get('urlSuffix'), 1);

        $routes = new RouteCollection();

        $defaults = [
            '_controller' => 'ContaoCoreBundle:Frontend:index'
        ];

        $pattern = '/{alias}';
        $require = ['alias' => '.*'];

        // URL suffix
        if ($suffix) {
            $pattern .= '.{_format}';

            $require['_format']  = $suffix;
            $defaults['_format'] = $suffix;
        }

        // Add language to URL
        if ($addlang) {
            $require['_locale'] = '[a-z]{2}(\-[A-Z]{2})?';

            $route = new Route('/{_locale}' . $pattern, $defaults, $require);
            $routes->add('contao_locale', $route);
        }

        // Default route
        $route = new Route($pattern, $defaults, $require);
        $routes->add('contao_default', $route);

        // Empty domain (root)
        $route = new Route('/', $defaults);
        $routes->add('contao_root', $route);

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'contao_frontend' === $type;
    }
}
