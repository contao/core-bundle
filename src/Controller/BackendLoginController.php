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

use Contao\BackendIndex;

/**
 * Backend controller to show login view.
 *
 * @author Tristan Lins <https://github.com/tristanlins>
 */
class BackendLoginController
{
    /**
     * Run the controller
     */
    public function runAction()
    {
        $controller = new BackendIndex();
        return $controller->run();
    }
}
