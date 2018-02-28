<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener\InsertTags;

use Symfony\Component\Translation\TranslatorInterface;

class TranslationListener
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Replaces the "trans" insert tag.
     *
     * @param string $tag
     *
     * @return string|false
     */
    public function onReplaceInsertTags(string $tag)
    {
        $chunks = explode('::', $tag);

        if ('trans' !== $chunks[0]) {
            return false;
        }

        $parameters = isset($chunks[2]) ? explode(':', $chunks[2]) : [];

        return $this->translator->trans($chunks[1], $parameters, $chunks[3] ?? null);
    }
}
