<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\EventListener\DataContainer;

use Contao\CoreBundle\EventListener\DataContainer\ThemeTemplatesListener;
use Contao\CoreBundle\Exception\InvalidThemePathException;
use Contao\CoreBundle\Tests\TestCase;
use Contao\CoreBundle\Twig\Loader\ContaoFilesystemLoader;
use Contao\CoreBundle\Twig\Loader\ThemeNamespace;
use Symfony\Contracts\Translation\TranslatorInterface;

class ThemeTemplatesListenerTest extends TestCase
{
    public function testRefreshesCache(): void
    {
        $filesystemLoader = $this->createMock(ContaoFilesystemLoader::class);
        $filesystemLoader
            ->expects($this->once())
            ->method('warmUp')
            ->with(true)
        ;

        $listener = $this->getListener($filesystemLoader);

        $this->assertSame('templates/foo/bar', $listener('templates/foo/bar'));
    }

    public function testThrowsFriendlyErrorMessageIfPathIsInvalid(): void
    {
        $themeNamespace = $this->createMock(ThemeNamespace::class);
        $themeNamespace
            ->method('generateSlug')
            ->with('<bad-path>')
            ->willThrowException(new InvalidThemePathException('<bad-path>', ['.', '_']))
        ;

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->with('ERR.invalidThemeTemplatePath', ['<bad-path>', '._'], 'contao_default')
            ->willReturn('<message>')
        ;

        $listener = $this->getListener(null, $themeNamespace, $translator);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('<message>');

        $listener('<bad-path>');
    }

    private function getListener(?ContaoFilesystemLoader $filesystemLoader = null, ?ThemeNamespace $themeNamespace = null, ?TranslatorInterface $translator = null): ThemeTemplatesListener
    {
        return new ThemeTemplatesListener(
            $filesystemLoader ?? $this->createMock(ContaoFilesystemLoader::class),
            $themeNamespace ?? $this->createMock(ThemeNamespace::class),
            $translator ?? $this->createMock(TranslatorInterface::class),
        );
    }
}
