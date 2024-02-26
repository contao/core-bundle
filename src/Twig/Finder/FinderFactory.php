<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Twig\Finder;

use Contao\CoreBundle\Twig\Loader\ContaoFilesystemLoader;
use Contao\CoreBundle\Twig\Loader\ThemeNamespace;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FinderFactory
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ContaoFilesystemLoader $filesystemLoader,
        private readonly ThemeNamespace $themeNamespace,
        private readonly TranslatorBagInterface|TranslatorInterface $translator,
    ) {
    }

    /**
     * Creates a new template finder instance.
     */
    public function create(): Finder
    {
        return new Finder($this->filesystemLoader, $this->themeNamespace, $this->translator);
    }
}
