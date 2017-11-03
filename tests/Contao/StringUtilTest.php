<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Contao;

use Contao\CoreBundle\Tests\TestCase;
use Contao\StringUtil;
use Contao\System;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @group contao3
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class StringUtilTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        \define('TL_ERROR', 'ERROR');
        \define('TL_ROOT', $this->getFixturesDir());

        $container = new ContainerBuilder();
        $container->set('monolog.logger.contao', new NullLogger());

        System::setContainer($container);
    }

    public function testGeneratesAliases(): void
    {
        $GLOBALS['TL_CONFIG']['characterSet'] = 'UTF-8';

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
     * @param string $string
     * @param array  $tokens
     * @param string $expected
     *
     * @dataProvider parseSimpleTokensProvider
     */
    public function testParsesSimpleTokens(string $string, array $tokens, string $expected): void
    {
        $this->assertSame($expected, StringUtil::parseSimpleTokens($string, $tokens));
    }

    /**
     * @return array
     */
    public function parseSimpleTokensProvider(): array
    {
        return [
            'Test regular token replacement' => [
                'This is my ##email##',
                ['email' => 'test@foobar.com'],
                'This is my test@foobar.com',
            ],
            'Test regular token replacement is non greedy' => [
                'This is my ##email##,##email2##',
                ['email' => 'test@foobar.com', 'email2' => 'foo@test.com'],
                'This is my test@foobar.com,foo@test.com',
            ],
            'Test token replacement with special characters (-)' => [
                'This is my ##e-mail##',
                ['e-mail' => 'test@foobar.com'],
                'This is my test@foobar.com',
            ],
            'Test token replacement with special characters (&)' => [
                'This is my ##e&mail##',
                ['e&mail' => 'test@foobar.com'],
                'This is my test@foobar.com',
            ],
            'Test token replacement with special characters (#)' => [
                'This is my ##e#mail##',
                ['e#mail' => 'test@foobar.com'],
                'This is my test@foobar.com',
            ],
            'Test token replacement with token delimiter (##)' => [
                'This is my ##e##mail##',
                ['e##mail' => 'test@foobar.com'],
                'This is my ##e##mail##',
            ],
            'Test comparisons (==) with regular characters (match)' => [
                'This is my {if email==""}match{endif}',
                ['email' => ''],
                'This is my match',
            ],
            'Test comparisons (==) with regular characters (no match)' => [
                'This is my {if email==""}match{endif}',
                ['email' => 'test@foobar.com'],
                'This is my ',
            ],
            'Test comparisons (!=) with regular characters (match)' => [
                'This is my {if email!=""}match{endif}',
                ['email' => 'test@foobar.com'],
                'This is my match',
            ],
            'Test comparisons (!=) with regular characters (no match)' => [
                'This is my {if email!=""}match{endif}',
                ['email' => ''],
                'This is my ',
            ],
            'Test comparisons (>) with regular characters (match)' => [
                'This is my {if value>0}match{endif}',
                ['value' => 5],
                'This is my match',
            ],
            'Test comparisons (>) with regular characters (no match)' => [
                'This is my {if value>0}hello{endif}',
                ['value' => -8],
                'This is my ',
            ],
            'Test comparisons (>=) with regular characters (match)' => [
                'This is my {if value>=0}match{endif}',
                ['value' => 5],
                'This is my match',
            ],
            'Test comparisons (>=) with regular characters (no match)' => [
                'This is my {if value>=0}hello{endif}',
                ['value' => -8],
                'This is my ',
            ],
            'Test comparisons (<) with regular characters (match)' => [
                'This is my {if value<0}match{endif}',
                ['value' => -5],
                'This is my match',
            ],
            'Test comparisons (<) with regular characters (no match)' => [
                'This is my {if value<0}hello{endif}',
                ['value' => 9],
                'This is my ',
            ],
            'Test comparisons (<=) with regular characters (match)' => [
                'This is my {if value<=0}match{endif}',
                ['value' => -5],
                'This is my match',
            ],
            'Test comparisons (<=) with regular characters (no match)' => [
                'This is my {if value<=0}hello{endif}',
                ['value' => 9],
                'This is my ',
            ],
            'Test comparisons (<) with special characters (match)' => [
                'This is my {if val&#ue<0}match{endif}',
                ['val&#ue' => -5],
                'This is my match',
            ],
            'Test comparisons (<) with special characters (no match)' => [
                'This is my {if val&#ue<0}match{endif}',
                ['val&#ue' => 9],
                'This is my ',
            ],
            'Test comparisons (===) with regular characters (match)' => [
                'This is my {if value===5}match{endif}',
                ['value' => 5],
                'This is my match',
            ],
            'Test comparisons (===) with regular characters (no match)' => [
                'This is my {if value===5}match{endif}',
                ['value' => 5.0],
                'This is my ',
            ],
            'Test comparisons (!==) with regular characters (match)' => [
                'This is my {if value!==5.0}match{endif}',
                ['value' => '5'],
                'This is my match',
            ],
            'Test comparisons (!==) with regular characters (no match)' => [
                'This is my {if value!==5.0}match{endif}',
                ['value' => 5.0],
                'This is my ',
            ],
            'Test whitespace in tokens not allowed and ignored' => [
                'This is my ##dumb token## you know',
                ['dumb token' => 'foobar'],
                'This is my ##dumb token## you know',
            ],
            'Test if-tags insertion not evaluated' => [
                '##token##',
                ['token' => '{if token=="foo"}'],
                '{if token=="foo"}',
            ],
            'Test if-tags insertion not evaluated with multiple tokens' => [
                '##token1####token2####token3##',
                ['token1' => '{', 'token2' => 'if', 'token3' => ' token=="foo"}'],
                '{if token=="foo"}',
            ],
            'Test nested if-tag with " in value (match)' => [
                '{if value=="f"oo"}1{endif}{if value=="f\"oo"}2{endif}',
                ['value' => 'f"oo'],
                '12',
            ],
            'Test else (match)' => [
                'This is my {if value=="foo"}match{else}else-match{endif}',
                ['value' => 'foo'],
                'This is my match',
            ],
            'Test else (no match)' => [
                'This is my {if value!="foo"}match{else}else-match{endif}',
                ['value' => 'foo'],
                'This is my else-match',
            ],
            'Test nested if (match)' => [
                '0{if value=="foo"}1{if value!="foo"}2{else}3{if value=="foo"}4{else}5{endif}6{endif}7{else}8{endif}9',
                ['value' => 'foo'],
                '0134679',
            ],
            'Test nested if (no match)' => [
                '0{if value!="foo"}1{if value=="foo"}2{else}3{if value!="foo"}4{else}5{endif}6{endif}7{else}8{endif}9',
                ['value' => 'foo'],
                '089',
            ],
            'Test nested elseif (match)' => [
                '0{if value=="bar"}1{elseif value=="foo"}2{else}3{if value=="bar"}4{elseif value=="foo"}5{else}6{endif}7{endif}8',
                ['value' => 'foo'],
                '028',
            ],
            'Test nested elseif (no match)' => [
                '0{if value=="bar"}1{elseif value!="foo"}2{else}3{if value=="bar"}4{elseif value!="foo"}5{else}6{endif}7{endif}8',
                ['value' => 'foo'],
                '03678',
            ],
            'Test special value chars \'=!<>;$()[] (match)' => [
                '{if value=="\'=!<>;$()[]"}match{else}no-match{endif}',
                ['value' => '\'=!<>;$()[]'],
                'match',
            ],
            'Test special value chars \'=!<>;$()[] (no match)' => [
                '{if value=="\'=!<>;$()[]"}match{else}no-match{endif}',
                ['value' => '=!<>;$()[]'],
                'no-match',
            ],
            'Test every elseif expression is skipped if first if statement evaluates to true' => [
                '{if value=="foobar"}Output 1{elseif value=="foobar"}Output 2{elseif value=="foobar"}Output 3{elseif value=="foobar"}Output 4{else}Output 5{endif}',
                ['value' => 'foobar'],
                'Output 1',
            ],
            'Test every elseif expression is skipped if first elseif statement evaluates to true' => [
                '{if value!="foobar"}Output 1{elseif value=="foobar"}Output 2{elseif value=="foobar"}Output 3{elseif value=="foobar"}Output 4{elseif value=="foobar"}Output 5{else}Output 6{endif}',
                ['value' => 'foobar'],
                'Output 2',
            ],
            'Test every elseif expression is skipped if second elseif statement evaluates to true' => [
                '{if value!="foobar"}Output 1{elseif value!="foobar"}Output 2{elseif value=="foobar"}Output 3{elseif value=="foobar"}Output 4{elseif value=="foobar"}Output 5{elseif value=="foobar"}Output 6{else}Output 7{endif}',
                ['value' => 'foobar'],
                'Output 3',
            ],
            'Test {{iflng}} insert tag or similar constructs are ignored' => [
                '{if value=="foobar"}{{iflng::en}}hi{{iflng}}{{elseifinserttag::whodoesthisanyway}}{elseif value=="foo"}{{iflng::en}}hi2{{iflng}}{else}ok{endif}',
                ['value' => 'foobar'],
                '{{iflng::en}}hi{{iflng}}{{elseifinserttag::whodoesthisanyway}}',
            ],
        ];
    }

    /**
     * @param string $string
     * @param array  $tokens
     * @param string $expected
     *
     * @dataProvider parseSimpleTokensCorrectNewlines
     */
    public function testHandlesLineBreaksWhenParsingSimpleTokens(string $string, array $tokens, string $expected): void
    {
        $this->assertSame($expected, StringUtil::parseSimpleTokens($string, $tokens));
    }

    /**
     * @return array
     */
    public function parseSimpleTokensCorrectNewlines(): array
    {
        return [
            'Test newlines are kept end of token' => [
                "This is my ##token##\n",
                ['token' => 'foo'],
                "This is my foo\n",
            ],
            'Test newlines are kept end in token' => [
                'This is my ##token##',
                ['token' => "foo\n"],
                "This is my foo\n",
            ],
            'Test newlines are kept end in and after token' => [
                "This is my ##token##\n",
                ['token' => "foo\n"],
                "This is my foo\n\n",
            ],
            'Test newlines are kept' => [
                "This is my \n ##newline## here",
                ['newline' => "foo\nbar\n"],
                "This is my \n foo\nbar\n here",
            ],
            'Test newlines are removed after if tag' => [
                "\n{if token=='foo'}\nline2\n{endif}\n",
                ['token' => 'foo'],
                "\nline2\n",
            ],
            'Test newlines are removed after else tag' => [
                "\n{if token!='foo'}{else}\nline2\n{endif}\n",
                ['token' => 'foo'],
                "\nline2\n",
            ],
        ];
    }

    /**
     * @param string $string
     * @param bool
     *
     * @dataProvider parseSimpleTokensDoesntExecutePhp
     */
    public function testDoesNotExecutePhpCode(string $string): void
    {
        $this->assertSame($string, StringUtil::parseSimpleTokens($string, []));
    }

    /**
     * @return array
     */
    public function parseSimpleTokensDoesntExecutePhp(): array
    {
        return [
            '(<?php)' => [
                'This <?php var_dump() ?> is a test.',
                false,
            ],
            '(<?=)' => [
                'This <?= $var ?> is a test.',
                false,
            ],
            '(<?)' => [
                'This <? var_dump() ?> is a test.',
                version_compare(PHP_VERSION, '7.0.0', '>='),
            ],
            '(<%)' => [
                'This <% var_dump() ?> is a test.',
                version_compare(PHP_VERSION, '7.0.0', '>=') || !\in_array(strtolower(ini_get('asp_tags')), ['1', 'on', 'yes', 'true'], true),
            ],
            '(<script language="php">)' => [
                'This <script language="php"> var_dump() </script> is a test.',
                version_compare(PHP_VERSION, '7.0.0', '>='),
            ],
            '(<script language=\'php\'>)' => [
                'This <script language=\'php\'> var_dump() </script> is a test.',
                version_compare(PHP_VERSION, '7.0.0', '>='),
            ],
        ];
    }

    /**
     * @param array $tokens
     * @param bool
     *
     * @dataProvider parseSimpleTokensDoesntExecutePhpInToken
     */
    public function testDoesNotExecutePhpCodeInTokens(array $tokens): void
    {
        $this->assertSame($tokens['foo'], StringUtil::parseSimpleTokens('##foo##', $tokens));
    }

    /**
     * @return array
     */
    public function parseSimpleTokensDoesntExecutePhpInToken(): array
    {
        return [
            '(<?php)' => [
                ['foo' => 'This <?php var_dump() ?> is a test.'],
                false,
            ],
            '(<?=)' => [
                ['foo' => 'This <?= $var ?> is a test.'],
                false,
            ],
            '(<?)' => [
                ['foo' => 'This <? var_dump() ?> is a test.'],
                version_compare(PHP_VERSION, '7.0.0', '>='),
            ],
            '(<%)' => [
                ['foo' => 'This <% var_dump() ?> is a test.'],
                version_compare(PHP_VERSION, '7.0.0', '>=') || !\in_array(strtolower(ini_get('asp_tags')), ['1', 'on', 'yes', 'true'], true),
            ],
            '(<script language="php">)' => [
                ['foo' => 'This <script language="php"> var_dump() </script> is a test.'],
                version_compare(PHP_VERSION, '7.0.0', '>='),
            ],
            '(<script language=\'php\'>)' => [
                ['foo' => 'This <script language=\'php\'> var_dump() </script> is a test.'],
                version_compare(PHP_VERSION, '7.0.0', '>='),
            ],
        ];
    }

    public function testDoesNotExecutePhpCodeInCombinedTokens(): void
    {
        $this->assertSame('This is <?php echo "I am evil";?> evil', StringUtil::parseSimpleTokens('This is ##open####open2####close## evil', [
            'open' => '<',
            'open2' => '?php echo "I am evil";',
            'close' => '?>',
        ]));
    }

    /**
     * @param $string
     *
     * @dataProvider parseSimpleTokensInvalidComparison
     */
    public function testFailsIfTheComparisonOperatorIsInvalid($string): void
    {
        $this->expectException('InvalidArgumentException');

        StringUtil::parseSimpleTokens($string, ['foo' => 'bar']);
    }

    /**
     * @return array
     */
    public function parseSimpleTokensInvalidComparison(): array
    {
        return [
            'PHP constants are not allowed' => ['{if foo==__FILE__}{endif}'],
            'Not closed string (")' => ['{if foo=="bar}{endif}'],
            'Not closed string (\')' => ['{if foo==\'bar}{endif}'],
            'Additional chars after string ("/)' => ['{if foo=="bar"/}{endif}'],
            'Additional chars after string (\'/)' => ['{if foo==\'bar\'/}{endif}'],
            'Additional chars after string ("*)' => ['{if foo=="bar"*}{endif}'],
            'Additional chars after string (\'*)' => ['{if foo==\'bar\'*}{endif}'],
            'Unknown operator (=)' => ['{if foo="bar"}{endif}'],
            'Unknown operator (====)' => ['{if foo===="bar"}{endif}'],
            'Unknown operator (<==)' => ['{if foo<=="bar"}{endif}'],
        ];
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

    public function testFailsIfThePathHasNoTrailingSlash(): void
    {
        $this->expectException('InvalidArgumentException');

        StringUtil::stripRootDir($this->getFixturesDir());
    }
}
