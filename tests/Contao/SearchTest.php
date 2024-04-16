<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Contao;

use Contao\CoreBundle\Tests\TestCase;
use Contao\Search;

/**
 * @group legacy
 */
class SearchTest extends TestCase
{
    /**
     * @dataProvider compareUrlsProvider
     */
    public function testCompareUrls(string $moreCanonicalUrl, string $lessCanonicalUrl): void
    {
        $search = new \ReflectionClass(Search::class);
        $compareUrls = $search->getMethod('compareUrls');

        $this->assertLessThan(0, $compareUrls->invokeArgs(null, [$moreCanonicalUrl, $lessCanonicalUrl]));
        $this->assertGreaterThan(0, $compareUrls->invokeArgs(null, [$lessCanonicalUrl, $moreCanonicalUrl]));
        $this->assertSame(0, $compareUrls->invokeArgs(null, [$moreCanonicalUrl, $moreCanonicalUrl]));
        $this->assertSame(0, $compareUrls->invokeArgs(null, [$lessCanonicalUrl, $lessCanonicalUrl]));
    }

    public static function compareUrlsProvider(): iterable
    {
        yield ['foo/bar.html', 'foo/bar.html?query'];
        yield ['foo/bar.html', 'foo/bar/baz.html'];
        yield ['foo/bar.html', 'foo/bar-baz.html'];
        yield ['foo/bar.html', 'foo/barr.html'];
        yield ['foo/bar.html', 'foo/baz.html'];
        yield ['foo/bar-longer-url-but-no-query.html', 'foo/bar.html?query'];
        yield ['foo/bar-longer-url-but-less-slashes.html', 'foo/bar/baz.html'];
        yield ['foo.html?query/with/many/slashes/', 'foo/bar.html?query-without-slashes'];
    }

    /**
     * @dataProvider splitIntoWordsProvider
     */
    public function testSplitIntoWords(string $source, array $expectedWords): void
    {
        $search = new \ReflectionClass(Search::class);
        $method = $search->getMethod('splitIntoWords');

        $this->assertSame($expectedWords, $method->invokeArgs(null, [$source, '']));
    }

    public static function splitIntoWordsProvider(): iterable
    {
        yield ['Lorem-Ipsum dolor,sit`amet/consectetur.', ['lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur']];
        yield ['FÖO Bär bäß', ['foo', 'bar', 'bass']];
        yield ['Contrôl Fée bïr çæ BŒ', ['control', 'fee', 'bir', 'cae', 'boe']];
    }

    /**
     * @dataProvider getMatchVariantsProvider
     */
    public function testGetMatchVariants(string $text, array $matches, array $expectedWords): void
    {
        $this->assertSame($expectedWords, Search::getMatchVariants($matches, $text, 'en'));
    }

    public static function getMatchVariantsProvider(): iterable
    {
        yield [
            'FÖO Bär bäß',
            ['foo', 'bar', 'bass'],
            ['FÖO', 'Bär', 'bäß'],
        ];
        yield [
            'Contrôl Fée bïr çæ BŒ',
            ['control', 'fee', 'bir', 'cae', 'boe'],
            ['Contrôl', 'Fée', 'bïr', 'çæ', 'BŒ'],
        ];
        yield [
            'foo Foo fOO FOO föö',
            ['foo', 'doesNotExist', 'f', 'fo'],
            ['foo', 'Foo', 'fOO', 'FOO', 'föö'],
        ];
        yield [
            'Text (with) phrase-content',
            ['text with phrase', 'Phrase Content', '(WITH', 'does not exist'],
            ['Text (with) phrase', 'phrase-content', '(with'],
        ];
    }
}
