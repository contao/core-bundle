<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Exception;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * To be thrown when the installation is not secure enough.
 *
 * i.e.: the web root is incorrectly configured causing the request url to end with "/web".
 *
 * @author Christian Schiffler <https://github.com/discordier>
 */
class InsecureInstallationHttpException extends ServiceUnavailableHttpException
{
}
