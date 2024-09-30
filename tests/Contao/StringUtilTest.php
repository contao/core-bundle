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

use Contao\Config;
use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\CoreBundle\Tests\TestCase;
use Contao\Database;
use Contao\DcaExtractor;
use Contao\DcaLoader;
use Contao\FilesModel;
use Contao\Input;
use Contao\Model;
use Contao\Model\Registry;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Psr\Log\NullLogger;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\RequestStack;

class StringUtilTest extends TestCase
{
    use ExpectDeprecationTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', $this->getFixturesDir());
        $container->setParameter('kernel.cache_dir', $this->getFixturesDir().'/cache');
        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.charset', 'UTF-8');
        $container->setParameter('contao.insert_tags.allowed_tags', ['*']);
        $container->set('request_stack', new RequestStack());
        $container->set('contao.security.token_checker', $this->createMock(TokenChecker::class));
        $container->set('monolog.logger.contao', new NullLogger());
        $container->set('contao.insert_tag.parser', new InsertTagParser($this->createMock(ContaoFramework::class)));

        System::setContainer($container);
    }

    protected function tearDown(): void
    {
        $this->resetStaticProperties([Input::class, System::class, Registry::class, Model::class, Config::class, DcaLoader::class, Database::class, DcaExtractor::class]);
        unset($GLOBALS['TL_MODELS'], $GLOBALS['TL_MIME'], $GLOBALS['TL_TEST'], $GLOBALS['TL_LANG']);

        parent::tearDown();
    }

    public function testGeneratesAliases(): void
    {
        $this->assertSame('foo', StringUtil::generateAlias('foo'));
        $this->assertSame('foo', StringUtil::generateAlias('FOO'));
        $this->assertSame('foo-bar', StringUtil::generateAlias('foo bar'));
        $this->assertSame('foo-bar', StringUtil::generateAlias('%foo&bar~'));
        $this->assertSame('foo-bar', StringUtil::generateAlias('foo&amp;bar'));
        $this->assertSame('foo-bar', StringUtil::generateAlias('foo-{{link::12}}-bar'));
        $this->assertSame('id-123', StringUtil::generateAlias('123'));
        $this->assertSame('123foo', StringUtil::generateAlias('123foo'));
        $this->assertSame('foo123', StringUtil::generateAlias('foo123'));
    }

    /**
     * @dataProvider getBase32
     */
    public function testEncodeDecodeBase32(string $binary, string $base32): void
    {
        $this->assertSame($base32, StringUtil::encodeBase32($binary));
        $this->assertSame($binary, StringUtil::decodeBase32($base32));
        $this->assertSame($binary, StringUtil::decodeBase32(strtolower($base32)));
        $this->assertSame($binary, StringUtil::decodeBase32(strtr($base32, '01', 'ol')));
        $this->assertSame($binary, StringUtil::decodeBase32(strtr($base32, '01', 'OI')));

        $this->assertSame($binary, StringUtil::decodeBase32(StringUtil::encodeBase32($binary)));
        $this->assertSame($base32, StringUtil::decodeBase32(StringUtil::encodeBase32($base32)));

        $this->assertSame($binary.$binary, StringUtil::decodeBase32(StringUtil::encodeBase32($binary.$binary)));
        $this->assertSame($base32.$base32, StringUtil::decodeBase32(StringUtil::encodeBase32($base32.$base32)));
    }

    public function getBase32(): \Generator
    {
        yield ['', ''];
        yield [' ', '40'];
        yield ['0', '60'];
        yield ["\0", '00'];
        yield [" \0", '4000'];
        yield ["  \0", '40G00'];
        yield ["   \0", '40G2000'];
        yield ["    \0", '40G20800'];
        yield ["     \0", '40G2081000'];
        yield ["\x00\x80", '0200'];
        yield ["\x01\x80", '0600'];
        yield ["\x01\x00", '0400'];
        yield ["\x00\x01", '000G'];
        yield ['foo', 'CSQPY'];
        yield ["\0foo\0", '01K6YVR0'];
        yield ["\0\0foo\0\0", '0006CVVF0000'];
        yield ["\0\0\0foo\0\0\0", '00000SKFDW00000'];
        yield ["\0\0\0\0foo\0\0\0\0", '00000036DXQG000000'];
        yield ["\0\0\0\0\0foo\0\0\0\0\0", '00000000CSQPY00000000'];
        yield ["\x00\x44\x32\x14\xc7\x42\x54\xb6\x35\xcf\x84\x65\x3a\x56\xd7\xc6\x75\xbe\x77\xdf", '0123456789ABCDEFGHJKMNPQRSTVWXYZ'];
    }

    public function testEncodeDecodeBase32AllBytes(): void
    {
        for ($i = 0; $i < 256; ++$i) {
            $char = \chr($i);

            for ($length = 1; $length < 8; ++$length) {
                $data = str_repeat($char, $length);
                $this->assertSame($data, StringUtil::decodeBase32(StringUtil::encodeBase32($data)));
            }
        }

        for ($i = 0; $i < 32; ++$i) {
            $char = '0123456789ABCDEFGHJKMNPQRSTVWXYZ'[$i];

            for ($length = 8; $length < 17; $length += 8) {
                $data = str_repeat($char, $length);
                $this->assertSame($data, StringUtil::encodeBase32(StringUtil::decodeBase32($data)));
            }
        }

        $this->assertSame('00011111', StringUtil::encodeBase32(StringUtil::decodeBase32('oO0iLIl1')));
    }

    /**
     * @dataProvider getBase32Invalid
     */
    public function testThrowsForInvalidBase32(string $invalid): void
    {
        $this->expectException(\InvalidArgumentException::class);

        StringUtil::decodeBase32($invalid);
    }

    public function getBase32Invalid(): \Generator
    {
        yield [' '];
        yield ['-'];
        yield ["\0"];
        yield ['ö'];
        yield ['u'];
        yield ['U'];
        yield ['a '];
        yield ['a-'];
        yield ["a\0"];
        yield ['aö'];
        yield ['au'];
        yield ['aU'];
        yield [' a'];
        yield ['-a'];
        yield ["\0a"];
        yield ['öa'];
        yield ['ua'];
        yield ['Ua'];
    }

    public function testStripsTheRootDirectory(): void
    {
        $this->assertSame('', StringUtil::stripRootDir($this->getFixturesDir().'/'));
        $this->assertSame('', StringUtil::stripRootDir($this->getFixturesDir().'\\'));
        $this->assertSame('foo', StringUtil::stripRootDir($this->getFixturesDir().'/foo'));
        $this->assertSame('foo', StringUtil::stripRootDir($this->getFixturesDir().'\foo'));
        $this->assertSame('foo/', StringUtil::stripRootDir($this->getFixturesDir().'/foo/'));
        $this->assertSame('foo\\', StringUtil::stripRootDir($this->getFixturesDir().'\foo\\'));
        $this->assertSame('foo/bar', StringUtil::stripRootDir($this->getFixturesDir().'/foo/bar'));
        $this->assertSame('foo\bar', StringUtil::stripRootDir($this->getFixturesDir().'\foo\bar'));
        $this->assertSame('../../foo/bar', StringUtil::stripRootDir($this->getFixturesDir().'/../../foo/bar'));
    }

    public function testFailsIfThePathIsOutsideTheRootDirectory(): void
    {
        $this->expectException('InvalidArgumentException');

        StringUtil::stripRootDir('/foo');
    }

    public function testFailsIfThePathIsTheParentFolder(): void
    {
        $this->expectException('InvalidArgumentException');

        StringUtil::stripRootDir(\dirname($this->getFixturesDir()).'/');
    }

    public function testFailsIfThePathDoesNotMatch(): void
    {
        $this->expectException('InvalidArgumentException');

        StringUtil::stripRootDir($this->getFixturesDir().'foo/');
    }

    public function testFailsIfThePathEqualsTheRootDirectory(): void
    {
        $this->expectException('InvalidArgumentException');

        StringUtil::stripRootDir($this->getFixturesDir());
    }

    public function testHandlesFalseyValuesWhenDecodingEntities(): void
    {
        $this->assertSame('0', StringUtil::decodeEntities(0));
        $this->assertSame('0', StringUtil::decodeEntities('0'));
        $this->assertSame('', StringUtil::decodeEntities(''));
        $this->assertSame('', StringUtil::decodeEntities(false));
        $this->assertSame('', StringUtil::decodeEntities(null));
    }

    /**
     * @dataProvider trimsplitProvider
     */
    public function testSplitsAndTrimsStrings(string $pattern, string $string, array $expected): void
    {
        $this->assertSame($expected, StringUtil::trimsplit($pattern, $string));
    }

    public function trimsplitProvider(): \Generator
    {
        yield 'Test regular split' => [
            ',',
            'foo,bar',
            ['foo', 'bar'],
        ];

        yield 'Test split with trim' => [
            ',',
            " \n \r \t foo \n \r \t , \n \r \t bar \n \r \t ",
            ['foo', 'bar'],
        ];

        yield 'Test regex split' => [
            '[,;]',
            'foo,bar;baz',
            ['foo', 'bar', 'baz'],
        ];

        yield 'Test regex split with trim' => [
            '[,;]',
            " \n \r \t foo \n \r \t , \n \r \t bar \n \r \t ; \n \r \t baz \n \r \t ",
            ['foo', 'bar', 'baz'],
        ];

        yield 'Test split cache bug 1' => [
            ',',
            ',foo,,bar',
            ['', 'foo', '', 'bar'],
        ];

        yield 'Test split cache bug 2' => [
            ',,',
            'foo,,bar',
            ['foo', 'bar'],
        ];
    }

    /**
     * @dataProvider getRevertInputEncoding
     */
    public function testRevertInputEncoding(string $source, ?string $expected = null): void
    {
        Input::setGet('value', $source);
        $inputEncoded = Input::get('value');
        Input::setGet('value', null);

        // Test input encoding round trip
        $this->assertSame($expected ?? $source, StringUtil::revertInputEncoding($inputEncoded));
    }

    public function getRevertInputEncoding(): \Generator
    {
        yield ['foobar'];
        yield ['foo{{email::test@example.com}}bar'];
        yield ['{{date::...}}'];
        yield ["<>&\u{A0}<>&\u{A0}"];
        yield ['I <3 Contao'];
        yield ['Remove unexpected <span>HTML tags'];
        yield ['Keep non-HTML <tags> intact'];
        yield ['Basic [&] entities [nbsp]', "Basic & entities \u{A0}"];
        yield ["Cont\xE4o invalid UTF-8", "Cont\u{FFFD}o invalid UTF-8"];
    }

    /**
     * @param mixed $string
     *
     * @dataProvider validEncodingsProvider
     */
    public function testConvertsEncodingOfAString($string, string $toEncoding, string $expected, ?string $fromEncoding = null): void
    {
        $prevSubstituteCharacter = mb_substitute_character();

        // Enforce substitute character for these tests (see #5011)
        mb_substitute_character(0x3F);

        $result = StringUtil::convertEncoding($string, $toEncoding, $fromEncoding);

        $this->assertSame($expected, $result);

        mb_substitute_character($prevSubstituteCharacter);
    }

    public function validEncodingsProvider(): \Generator
    {
        yield 'From UTF-8 to ISO-8859-1' => [
            '𝚏ōȏճăᴦ',
            'ISO-8859-1',
            '??????',
            'UTF-8',
        ];

        yield 'From ISO-8859-1 to UTF-8' => [
            '𝚏ōȏճăᴦ',
            'UTF-8',
            'ðÅÈÕ³Äá´¦',
            'ISO-8859-1',
        ];

        yield 'From UTF-8 to ASCII' => [
            '𝚏ōȏճăᴦbaz',
            'ASCII',
            '??????baz',
            'UTF-8',
        ];

        yield 'Same encoding with UTF-8' => [
            '𝚏ōȏճăᴦ',
            'UTF-8',
            '𝚏ōȏճăᴦ',
            'UTF-8',
        ];

        yield 'Same encoding with ASCII' => [
            'foobar',
            'ASCII',
            'foobar',
            'ASCII',
        ];

        yield 'Empty string' => [
            '',
            'UTF-8',
            '',
        ];

        yield 'Integer argument' => [
            42,
            'UTF-8',
            '42',
            'ASCII',
        ];

        yield 'Integer argument with same encoding' => [
            42,
            'UTF-8',
            '42',
            'UTF-8',
        ];

        yield 'Float argument with same encoding' => [
            13.37,
            'ASCII',
            '13.37',
            'ASCII',
        ];

        yield 'String with blanks' => [
            '  ',
            'UTF-8',
            '  ',
        ];

        yield 'String "0"' => [
            '0',
            'UTF-8',
            '0',
        ];

        yield 'Stringable argument' => [
            new class('foobar') {
                private string $value;

                public function __construct(string $value)
                {
                    $this->value = $value;
                }

                public function __toString(): string
                {
                    return $this->value;
                }
            },
            'UTF-8',
            'foobar',
            'UTF-8',
        ];
    }

    /**
     * @param array|object $value
     *
     * @group legacy
     *
     * @dataProvider invalidEncodingsProvider
     */
    public function testReturnsEmptyStringAndTriggersDeprecationWhenEncodingNonStringableValues($value): void
    {
        $this->expectDeprecation('Since contao/core-bundle 4.9: Passing a non-stringable argument to StringUtil::convertEncoding() has been deprecated %s.');

        /** @phpstan-ignore argument.type */
        $result = StringUtil::convertEncoding($value, 'UTF-8');

        $this->assertSame('', $result);
    }

    public function invalidEncodingsProvider(): \Generator
    {
        yield 'Array' => [[]];
        yield 'Non-stringable object' => [new \stdClass()];
    }

    /**
     * @param float|int $source
     *
     * @dataProvider numberToStringProvider
     */
    public function testNumberToString($source, string $expected, ?int $precision = null): void
    {
        $this->assertSame($expected, StringUtil::numberToString($source, $precision));
    }

    public function numberToStringProvider(): \Generator
    {
        yield [0, '0'];
        yield [1, '1'];
        yield [-0, '0'];
        yield [-1, '-1'];
        yield [0.0, '0'];
        yield [1.0, '1'];
        yield [-0.0, '0'];
        yield [-1.0, '-1'];
        yield [0.00000000000000000000000000000000000000000000001, '0.00000000000000000000000000000000000000000000001'];
        yield [1000000000000000000000000000000000000000000000000, '1000000000000000000000000000000000000000000000000'];
        yield [123456789012345678901234567890, '123456789012350000000000000000'];
        yield [PHP_INT_MAX, '9223372036854775807'];
        yield [PHP_INT_MAX, '9223400000000000000', 5];
        yield [(float) PHP_INT_MAX, '9223372036854800000'];
        yield [PHP_FLOAT_EPSILON, '0.00000000000000022204460492503'];
        yield [PHP_FLOAT_MIN, '0.'.str_repeat('0', 307).'22250738585072'];
        yield [PHP_FLOAT_MAX, '17976931348623'.str_repeat('0', 295)];
        yield [1.23456, '1.23456', -1];
        yield [1.23456, '1.2', 2];
    }

    /**
     * @param float|int $source
     *
     * @dataProvider numberToStringFailsProvider
     */
    public function testNumberToStringFails($source, string $exception, ?int $precision = null): void
    {
        $this->expectException($exception);

        StringUtil::numberToString($source, $precision);
    }

    public function numberToStringFailsProvider(): \Generator
    {
        yield [INF, \InvalidArgumentException::class];
        yield [NAN, \InvalidArgumentException::class];
        yield [PHP_FLOAT_MAX * PHP_FLOAT_MAX, \InvalidArgumentException::class];
        yield ['string', \TypeError::class];
        yield [1.2, \InvalidArgumentException::class, -2];
        yield [1.2, \InvalidArgumentException::class, 0];
        yield [1.2, \InvalidArgumentException::class, 1];
    }

    public function testInsertTagToSrc(): void
    {
        $container = System::getContainer();
        $finder = new ResourceFinder(Path::join($this->getFixturesDir(), 'vendor/contao/test-bundle/Resources/contao'));
        $locator = new FileLocator(Path::join($this->getFixturesDir(), 'vendor/contao/test-bundle/Resources/contao'));

        $container->set('database_connection', $this->createMock(Connection::class));
        $container->set('contao.resource_finder', $finder);
        $container->set('contao.resource_locator', $locator);

        $GLOBALS['TL_MODELS']['tl_files'] = FilesModel::class;

        $file = new FilesModel([
            'id' => 1,
            'tstamp' => time(),
            'uuid' => StringUtil::uuidToBin('9e474bae-ce18-11ec-9465-cadae3e5cf5d'),
            'type' => 'file',
            'path' => 'files/test.jpg',
            'extension' => 'jpg',
            'name' => 'test.jpg',
        ]);

        Registry::getInstance()->registerAlias($file, 'uuid', $file->uuid);

        $this->assertSame(
            'Foo <img src="files/test.jpg" /> Bar',
            StringUtil::insertTagToSrc('Foo <img src="{{file::9e474bae-ce18-11ec-9465-cadae3e5cf5d}}" /> Bar')
        );

        $this->assertSame(
            'Foo <img src="{{file::##simple-token##|urlattr}}" /> Bar',
            StringUtil::insertTagToSrc('Foo <img src="{{file::##simple-token##|urlattr}}" /> Bar')
        );
    }
}
