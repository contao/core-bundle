<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Slug;

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\SlugValidCharactersEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ValidCharacters
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string[]
     */
    private $defaultOptions = [
        '\pN\p{Ll}' => 'unicodeLowercase',
        '\pN\pL' => 'unicode',
        '0-9a-z' => 'asciiLowercase',
        '0-9a-zA-Z' => 'ascii',
    ];

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param TranslatorInterface      $translator
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, TranslatorInterface $translator)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
    }

    /**
     * Returns the options for the valid characters setting suitable for widgets.
     *
     * @return array
     */
    public function getOptions(): array
    {
        $options = [];

        foreach ($this->defaultOptions as $option => $label) {
            $options[$option] = $this->translator->trans('MSC.validCharacters.'.$label, [], 'contao_default');
        }

        $event = new SlugValidCharactersEvent($options);

        $this->eventDispatcher->dispatch(ContaoCoreEvents::SLUG_VALID_CHARACTERS, $event);

        return $event->getOptions();
    }
}
