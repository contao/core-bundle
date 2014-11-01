<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\EventListener;

use Contao\Input;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class ParameterFilterListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request         = $event->getRequest();
        $tlScript        = $request->attributes->get('_tl_script');
        $autoItemValue   = $request->attributes->get('_auto_item');
        $parameterString = substr($request->attributes->get('_path_parameters'), 1);

        // Define constant TL_SCRIPT for backwards compatibility
        if ($tlScript) {
            define('TL_SCRIPT', $tlScript);
        } elseif (!defined('TL_SCRIPT')) {
            define('TL_SCRIPT', 'index.php');
        }

        // Set the auto_item query parameter from routing attributes
        if ($autoItemValue) {
            // TODO use $request here?!?!
            Input::setGet('auto_item', $autoItemValue, true);
            // $request->query->set('auto_item', $autoItemValue);
        }

        // Set the path parameters from routing attributes
        if ($parameterString) {
            $parts = explode('/', $parameterString);

            while (count($parts)) {
                $key   = array_shift($parts);
                $value = array_shift($parts);

                // TODO use $request here?!?!
                Input::setGet($key, $value, true);
                // $request->query->set($key, $value);
            }
        }
    }
}
