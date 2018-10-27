<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\EventListener\InsertTags;

use Symfony\Component\Translation\TranslatorInterface;

class TranslationListener
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Replaces the "trans" insert tag.
     *
     * @return string|false
     */
    public function onReplaceInsertTags(string $tag)
    {
        $chunks = explode('::', $tag);

        if ('trans' !== $chunks[0]) {
            return false;
        }

        $parameters = isset($chunks[3]) ? explode(':', $chunks[3]) : [];

        return $this->translator->trans($chunks[1], $parameters, $chunks[2] ?? null);
    }
}
