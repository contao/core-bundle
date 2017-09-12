<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Translation;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Translator.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class Translator implements TranslatorInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @param TranslatorInterface      $translator Original translator service that gets decorated.
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(TranslatorInterface $translator, ContaoFrameworkInterface $framework)
    {
        $this->translator = $translator;
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $translated = $this->translator->trans($id, $parameters, $domain, $locale);

        if ($translated !== $id) {
            return $translated;
        }

        if (!$this->framework->isInitialized()) {
            return $translated;
        }

        $this->loadLanguageFile($domain);

        if (null !== $translated = $this->getFromGlobals($id, $domain)) {
            return $translated;
        }

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        return $this->translator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->translator->getLocale();
    }

    /**
     * Get the translation from the $GLOBALS['TL_LANG'] array.
     *
     * @param string      $id     Message id, e.g. "MSC.view"
     * @param string|null $domain Message domain, e.g. "messages" or "tl_content"
     *
     * @return string|null
     */
    private function getFromGlobals(string $id, string $domain = null)
    {
        if ('messages' !== $domain && null !== $domain) {
            $id = $domain.'.'.$id;
        }

        preg_match_all('/(?:\\\\.|[^.])+/s', $id, $matches);
        $parts = preg_replace('/\\\\(.)/s', '$1', $matches[0]);

        $item = &$GLOBALS['TL_LANG'];

        foreach ($parts as $part) {
            if (!isset($item[$part])) {
                return null;
            }
            $item = &$item[$part];
        }

        return $item;
    }

    /**
     * Load a Contao framework language file.
     *
     * @param string $name
     */
    private function loadLanguageFile(string $name = null)
    {
        if ('messages' === $name || null === $name) {
            $name = 'default';
        }

        /** @var \Contao\System */
        $system = $this->framework->getAdapter('System');

        $system->loadLanguageFile($name);
    }
}
