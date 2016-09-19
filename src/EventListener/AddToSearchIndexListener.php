<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Frontend;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Adds a page to the search index after the response has been sent.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class AddToSearchIndexListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var array
     */
    private $ignorePathRegexes = [];

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @return mixed
     */
    public function getIgnorePathRegexes()
    {
        return $this->ignorePathRegexes;
    }

    /**
     * @param array $ignorePathRegexes
     *
     * @return AddToSearchIndexListener
     */
    public function setIgnorePathRegexes(array $ignorePathRegexes)
    {
        $this->ignorePathRegexes = $ignorePathRegexes;

        return $this;
    }

    /**
     * @param string $rgxp
     *
     * @return AddToSearchIndexListener
     */
    public function addIgnorePathRegex($rgxp)
    {
        $this->ignorePathRegexes[] = $rgxp;

        return $this;
    }

    /**
     * Forwards the request to the Frontend class if there is a page object.
     *
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        if (!$this->framework->isInitialized()) {
            return;
        }

        if (0 !== count($this->ignorePathRegexes)) {
            foreach ($this->ignorePathRegexes as $regex) {
                if (preg_match($regex, $event->getRequest()->getPathInfo())) {
                    return;
                }
            }
        }

        /** @var Frontend $frontend */
        $frontend = $this->framework->getAdapter('Contao\Frontend');
        $frontend->indexPageIfApplicable($event->getResponse());
    }
}
