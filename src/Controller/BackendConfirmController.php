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

use Contao\BackendConfirm;

/**
 * Backend controller to show the confirm view.
 *
 * @author Tristan Lins <https://github.com/tristanlins>
 */
class BackendConfirmController
{
    /**
     * Run the controller
     */
    public function runAction()
    {
        $controller = new BackendConfirm();
        return $controller->run();
    }
}
