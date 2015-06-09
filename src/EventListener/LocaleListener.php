<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener;

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
    use ScopeAwareTrait;

    /**
     * @var array
     */
    private $availableLocales;

    /**
     * Constructor.
     *
     * @param array $availableLocales The available locales
     */
    public function __construct($availableLocales)
    {
        $this->availableLocales = $availableLocales;
    }

    /**
     * Sets the default locale based on the request or session.
     *
     * @param GetResponseEvent $event The event object
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$this->isContaoScope()) {
            return;
        }

        $request = $event->getRequest();
        $locale  = $this->getLocale($request);

        $request->attributes->set('_locale', $locale);

        if ($request->hasSession()) {
            $request->getSession()->set('_locale', $locale);
        }
    }

    /**
     * Returns the locale from the request, the session or the HTTP header.
     *
     * @param Request $request The request object
     *
     * @return string The locale
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
     * @param string $locale The locale string
     *
     * @return string The formatted locale
     *
     * @throw \InvalidArgumentException If the given locale is not supported
     */
    private function formatLocaleId($locale)
    {
        if (!preg_match('/^[a-z]{2}([_-][a-z]{2})?$/i', $locale)) {
            throw new \InvalidArgumentException('"' . $locale . '" is not a supported locale.');
        }

        $values = preg_split('/-|_/', $locale);
        $locale = strtolower($values[0]);

        if (isset($values[1])) {
            $locale .= '_' . strtoupper($values[1]);
        }

        return $locale;
    }

    /**
     * Creates a new instance with the installed languages.
     *
     * @param string $defaultLocale The default locale
     * @param string $rootDir       The kernel root directory
     *
     * @return static The new object instance
     */
    public static function createWithLocales($defaultLocale, $rootDir)
    {
        $dirs = [__DIR__ . '/../Resources/contao/languages'];

        // app/Resources/contao/languages
        if (is_dir($rootDir . '/Resources/contao/languages')) {
            $dirs[] = $rootDir . '/Resources/contao/languages';
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

        return new static(array_unique($languages));
    }
}
