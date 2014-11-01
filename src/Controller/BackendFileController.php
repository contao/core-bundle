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

use Contao\BackendFile;

/**
 * Backend controller to show the file selection view.
 *
 * @author Tristan Lins <https://github.com/tristanlins>
 */
class BackendFileController
{
    /**
     * Run the controller
     */
    public function runAction()
    {
        $controller = new BackendFile();
        return $controller->run();
    }
}
