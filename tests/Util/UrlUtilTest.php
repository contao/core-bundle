<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Util;

use Contao\CoreBundle\Tests\TestCase;
use Contao\CoreBundle\Util\UrlUtil;

class UrlUtilTest extends TestCase
{
    /**
     * @dataProvider getMakeAbsolute
     */
    public function testMakeAbsolute(string $url, string $base, string $expected): void
    {
        $this->assertSame($expected, UrlUtil::makeAbsolute($url, $base));
    }

    public function getMakeAbsolute(): \Generator
    {
        $fullUrl = 'https://user:pass@host:123/path/file?query#fragment';

        yield ['', $fullUrl, 'https://user:pass@host:123/path/file?query'];
        yield ['#foo', $fullUrl, 'https://user:pass@host:123/path/file?query#foo'];
        yield ['?foo', $fullUrl, 'https://user:pass@host:123/path/file?foo'];
        yield ['?foo#bar', $fullUrl, 'https://user:pass@host:123/path/file?foo#bar'];
        yield ['/foo', $fullUrl, 'https://user:pass@host:123/foo'];
        yield ['/foo?bar', $fullUrl, 'https://user:pass@host:123/foo?bar'];
        yield ['/foo?bar#baz', $fullUrl, 'https://user:pass@host:123/foo?bar#baz'];
        yield ['foo', $fullUrl, 'https://user:pass@host:123/path/foo'];
        yield ['foo?bar', $fullUrl, 'https://user:pass@host:123/path/foo?bar'];
        yield ['foo?bar#baz', $fullUrl, 'https://user:pass@host:123/path/foo?bar#baz'];
        yield ['./foo', $fullUrl, 'https://user:pass@host:123/path/foo'];
        yield ['./foo/../bar', $fullUrl, 'https://user:pass@host:123/path/bar'];
        yield ['../foo', $fullUrl, 'https://user:pass@host:123/foo'];
        yield ['https://example.com', $fullUrl, 'https://example.com/'];
        yield ['http://example.com', $fullUrl, 'http://example.com/'];
        yield ['//example.com', $fullUrl, 'https://example.com/'];
        yield ['foo', 'https://example.com/path/file/', 'https://example.com/path/file/foo'];
        yield ['../foo', 'https://example.com/', 'https://example.com/foo'];
        yield ['../foo', 'https://example.com', 'https://example.com/foo'];
        yield ['foo', '//example.com/path/file', '//example.com/path/foo'];
        yield ['foo', '/c413/public/', '/c413/public/foo'];
        yield ['../foo', '/c413/public/', '/c413/foo'];
        yield ['?foo', '/c413/public/', '/c413/public/?foo'];
        yield ['#foo', '/c413/public/', '/c413/public/#foo'];
        yield ['foo', '', '/foo'];
        yield ['../foo', '', '/foo'];
        yield ['?foo', '', '/?foo'];
        yield ['#foo', '', '/#foo'];
        yield ['HTTPS://example.com', '', 'https://example.com/'];
        yield ['', 'HTTPS://example.com', 'https://example.com/'];
        yield ['mailto:mail@example.com?subject=hi', '', 'mailto:mail@example.com?subject=hi'];
        yield ['tel:+1234-56789', '', 'tel:+1234-56789'];
        yield ['data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', '', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'];
    }

    public function testMakeAbsoluteFailsForRelativeBasePath(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        UrlUtil::makeAbsolute('foo', 'path/foo');
    }
}
