<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao\Bundle\CoreBundle\EventListener;

use Contao\Config;
use Contao\Environment;
use Contao\Search;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Adds a page to the search index
 *
 * @author Leo Feyer <https://contao.org>
 */
class AddToSearchIndexListener
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
     * Adds a page to the search index
     *
     * @param PostResponseEvent $event The event object
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        global $objPage;

	    if (null === $objPage) {
		    return;
	    }

        // Index page if searching is allowed and there is no back end user
        if ($this->config->get('enableSearch') && 'regular' === $objPage->type && !BE_USER_LOGGED_IN && !$objPage->noSearch) {

            // Index protected pages if enabled
            if ($this->config->get('indexProtected') || (!FE_USER_LOGGED_IN && !$objPage->protected)) {
                $blnIndex = true;

                // Do not index the page if certain parameters are set
                foreach (array_keys($_GET) as $key) {
                    if (in_array($key, $GLOBALS['TL_NOINDEX_KEYS']) || 0 === strncmp($key, 'page_', 5)) {
                        $blnIndex = false;
                        break;
                    }
                }

                if ($blnIndex) {
                    $arrData = [
                        'url'       => Environment::get('request'),
                        'content'   => $event->getResponse()->getContent(),
                        'title'     => $objPage->pageTitle ?: $objPage->title,
                        'protected' => ($objPage->protected ? '1' : ''),
                        'groups'    => $objPage->groups,
                        'pid'       => $objPage->id,
                        'language'  => $objPage->language
                    ];

                    Search::indexPage($arrData);
                }
            }
        }
    }
}
