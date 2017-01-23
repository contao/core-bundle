<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Routing\RequestContext;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Persists the locale from the accept header or the request in the session.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class LocaleListener
{
    /**
     * @var RequestContext
     */
    private $requestContext;

    /**
     * @var array
     */
    private $availableLocales;

    /**
     * Constructor.
     *
     * @param RequestContext $requestContext
     * @param array          $availableLocales
     */
    public function __construct(RequestContext $requestContext, $availableLocales)
    {
        $this->availableLocales = $availableLocales;
        $this->requestContext = $requestContext;
    }

    /**
     * Sets the default locale based on the request or session.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$this->requestContext->isContaoRequest($event->getRequest())) {
            return;
        }

        $request = $event->getRequest();
        $locale = $this->getLocale($request);

        $request->attributes->set('_locale', $locale);

        if ($request->hasSession()) {
            $request->getSession()->set('_locale', $locale);
        }
    }

    /**
     * Returns the locale from the request, the session or the HTTP header.
     *
     * @param Request $request
     *
     * @return string
     */
    private function getLocale(Request $request)
    {
        if (null !== $request->attributes->get('_locale')) {
            return $this->formatLocaleId($request->attributes->get('_locale'));
        }

        if (null !== ($session = $request->getSession()) && $session->has('_locale')) {
            return $session->get('_locale');
        }

        return $request->getPreferredLanguage($this->availableLocales);
    }

    /**
     * Formats a string to represent a locale ID.
     *
     * @param string $locale
     *
     * @return string
     *
     * @throw \InvalidArgumentException
     */
    private function formatLocaleId($locale)
    {
        if (!preg_match('/^[a-z]{2}([_-][a-z]{2})?$/i', $locale)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a supported locale.', $locale));
        }

        $values = preg_split('/-|_/', $locale);
        $locale = strtolower($values[0]);

        if (isset($values[1])) {
            $locale .= '_'.strtoupper($values[1]);
        }

        return $locale;
    }

    /**
     * Creates a new instance with the installed languages.
     *
     * @param RequestContext $requestContext
     * @param string         $defaultLocale
     * @param string         $rootDir
     *
     * @return static
     */
    public static function createWithLocales(RequestContext $requestContext, $defaultLocale, $rootDir)
    {
        $dirs = [__DIR__.'/../Resources/contao/languages'];

        // app/Resources/contao/languages
        if (is_dir($rootDir.'/Resources/contao/languages')) {
            $dirs[] = $rootDir.'/Resources/contao/languages';
        }

        $finder = Finder::create()->directories()->depth(0)->in($dirs);

        $languages = array_values(
            array_map(
                function (SplFileInfo $file) {
                    return $file->getFilename();
                },
                iterator_to_array($finder)
            )
        );

        // The default locale must be the first supported language (see contao/core#6533)
        array_unshift($languages, $defaultLocale);

        return new static($requestContext, array_unique($languages));
    }
}
