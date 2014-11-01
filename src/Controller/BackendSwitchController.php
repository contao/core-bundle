<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\CoreBundle\Controller;

use Contao\BackendSwitch;

/**
 * Backend controller to show the switch view.
 *
 * @author Tristan Lins <https://github.com/tristanlins>
 */
class BackendSwitchController
{
    /**
     * Run the controller
     */
    public function runAction()
    {
        $controller = new BackendSwitch();
        return $controller->run();
    }
}
