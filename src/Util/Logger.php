<?php
/*
 * This file is part of core-bundle
 * 
 * Copyright (c) CTS GmbH
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 */

namespace Contao\CoreBundle\Util;

use Psr\Log\AbstractLogger;


/**
 * psr-3 compatible log wrapper for contao
 * @author Daniel Schwiperich <d.schwiperich@cts-media.eu>
 */
class Logger extends AbstractLogger
{
    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        \System::log($message, __METHOD__, $level);
    }


}