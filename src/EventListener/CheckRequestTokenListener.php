<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\Environment;
use Contao\Input;
use Contao\RequestToken;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Checks the Contao request token
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class CheckRequestTokenListener
{
    /**
     * Initializes the system upon kernel.request.
     *
     * @param GetResponseEvent $event The event object
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        RequestToken::initialize();

        // Check the request token upon POST requests
        if (!$_POST || RequestToken::validate(Input::post('REQUEST_TOKEN'))) {
            return;
        }

        // Force a JavaScript redirect upon Ajax requests (IE requires absolute link)
        if (Environment::get('isAjaxRequest')) {
            header('HTTP/1.1 204 No Content');
            header('X-Ajax-Location: ' . Environment::get('base') . 'contao/');
            exit;
        } else {
            header('HTTP/1.1 400 Bad Request');
            die_nicely(
                'be_referer',
                'Invalid request token. Please <a href="javascript:window.location.href=window.location.href">go back</a> and try again.'
            );
        }
    }
}
