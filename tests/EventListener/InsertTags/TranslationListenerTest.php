<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\EventListener\InsertTags;

use Contao\CoreBundle\EventListener\InsertTags\TranslationListener;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

class TranslationListenerTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $listener = new TranslationListener($this->createMock(TranslatorInterface::class));

        $this->assertInstanceOf('Contao\CoreBundle\EventListener\InsertTags\TranslationListener', $listener);
    }

    /**
     * @dataProvider insertTagsProvider
     */
    public function testReplacesInsertTagsWithTranslation(string $id, ?string $domain, array $parameters, string $result): void
    {
        $translator = $this->createMock(TranslatorInterface::class);

        $translator
            ->expects($this->once())
            ->method('trans')
            ->with($id, $parameters, $domain)
            ->willReturn($result)
        ;

        $listener = new TranslationListener($translator);

        if (null === $domain) {
            $insertTag = sprintf('trans::%s', $id);
        } elseif (empty($parameters)) {
            $insertTag = sprintf('trans::%s::%s', $id, $domain);
        } else {
            $insertTag = sprintf('trans::%s::%s::%s', $id, $domain, implode(':', $parameters));
        }

        $this->assertSame($result, $listener->onReplaceInsertTags($insertTag));
    }

    public function insertTagsProvider()
    {
        return [
            ['foo', null, [], 'bar'],
            ['foo', 'bar', [], 'baz'],
            ['foo', 'bar', ['baz', 'what'], 'else'],
        ];
    }

    public function testIgnoresOtherInsertTags(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);

        $translator
            ->expects($this->never())
            ->method('trans')
        ;

        $translator
            ->expects($this->never())
            ->method('transChoice')
        ;

        $listener = new TranslationListener($translator);

        $this->assertFalse($listener->onReplaceInsertTags('env::pageTitle'));
    }
}
