<?php

namespace Contao\CoreBundle\Tests\Routing;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Routing\RouteProvider;
use Contao\CoreBundle\Tests\TestCase;
use Contao\Model\Collection;
use Contao\PageModel;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class RouteProviderTest extends TestCase
{
    /**
     * @var MockObject|ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var MockObject|Request
     */
    private $request;

    /**
     * @var MockObject
     */
    private $input;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();

        $this->framework = $this->mockContaoFramework();
        $this->request = $this->createMock(Request::class);
        $this->input = $this->createPartialMock(Adapter::class, ['get', 'setGet']);

        require_once __DIR__.'/../../src/Resources/contao/helper/functions.php';

        $_GET = [];
        $GLOBALS['TL_AUTO_ITEM'] = [];
        $GLOBALS['TL_HOOKS']['getPageIdFromUrl'] = [];
    }

    public function testReturnsNullOnEmptyUrl()
    {
        $provider = new RouteProvider($this->framework, $this->input, '.html', false, false, true);

        $this->input->expects($this->never())->method('setGet');

        $this->request->method('getPathInfo')->willReturn('/');

        $this->assertNull($provider->getPageIdFromUrl($this->request));
    }

    public function testThrowsExceptionWithAutoItemInUrl()
    {
        $provider = new RouteProvider($this->framework, $this->input, '.html', false, false, true);

        $this->input->expects($this->never())->method('setGet');

        $this->request->method('getPathInfo')->willReturn('/test/auto_item/foobar.html');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The request string must not contain "auto_item"');

        $provider->getPageIdFromUrl($this->request);
    }

    public function testThrowsExceptionIfUrlSuffixDoesNotMatch()
    {
        $provider = new RouteProvider($this->framework, $this->input, '.html', false, false, true);

        $this->input->expects($this->never())->method('setGet');

        $this->request->method('getPathInfo')->willReturn('/foobar.html5');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The URL suffix does not match');

        $provider->getPageIdFromUrl($this->request);
    }

    /**
     * @dataProvider languageInUrlProvider
     */
    public function testReturnsNullIfOnlyLanguageIsInUrl($pathInfo, $language)
    {
        $provider = new RouteProvider($this->framework, $this->input, '.html', true, false, true);

        $this->input
            ->expects($this->once())
            ->method('setGet')
            ->with('language', $language);

        $this->request->method('getPathInfo')->willReturn($pathInfo);

        $this->assertNull($provider->getPageIdFromUrl($this->request));
    }

    public function languageInUrlProvider()
    {
        return [
            ['/en/', 'en'],
            ['/de/', 'de'],
            ['/de-CH/', 'de-CH'],
            ['/fr-IT/', 'fr-IT'],
        ];
    }

    /**
     * @dataProvider missingLanguageProvider
     */
    public function testThrowsExceptionIfLanguageIsMissing($pathInfo)
    {
        $provider = new RouteProvider($this->framework, $this->input, '.html', true, false, true);

        $this->input->expects($this->never())->method('setGet');

        $this->request->method('getPathInfo')->willReturn($pathInfo);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Language not provided');

        $provider->getPageIdFromUrl($this->request);
    }

    public function missingLanguageProvider()
    {
        return [
            ['/foobar.html'],
            ['/foo/foobar.html'],
            ['/de_DE/foobar.html'],
        ];
    }

    /**
     * @dataProvider urlWithSlashOnlyProvider
     */
    public function testThrowsExceptionIfUrlIsSlashOnly($pathInfo, $language)
    {
        $provider = new RouteProvider($this->framework, $this->input, '.html', (bool) $language, false, true);

        $this->input
            ->expects($language ? $this->once() : $this->never())
            ->method('setGet')
            ->with('language', $language);

        $this->request->method('getPathInfo')->willReturn($pathInfo);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Did not find a matching page');

        $provider->getPageIdFromUrl($this->request);
    }

    public function urlWithSlashOnlyProvider()
    {
        return [
            ['/en//.html', 'en'],
            ['/de-CH//.html', 'de-CH'],
            ['//.html', ''],
        ];
    }

    public function testThrowsExceptionIfAliasIsEmpty()
    {
        $provider = new RouteProvider($this->framework, $this->input, '.html', false, false, true);

        $this->input->expects($this->never())->method('setGet');

        $this->request->method('getPathInfo')->willReturn('//foobar.html');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The alias is empty');

        $provider->getPageIdFromUrl($this->request);
    }

    public function testTestSkipsEmptyParameters()
    {
        $provider = new RouteProvider($this->framework, $this->input, '.html', true, false, true);

        $this
            ->input
            ->expects($this->exactly(2))
            ->method('setGet')
            ->withConsecutive(['language', 'de'], ['bar', 'baz', true]);

        $this->request->method('getPathInfo')->willReturn('/de/foo//bar/bar/baz.html');

        $this->assertSame('foo', $provider->getPageIdFromUrl($this->request));
    }

    /**
     * @dataProvider getParameterExistsProvider
     */
    public function testThrowsExceptionIfGetParameterExists($pathInfo, $language, $getKey)
    {
        $provider = new RouteProvider($this->framework, $this->input, '.html', (bool) $language, false, true);

        $this->input
            ->expects($language ? $this->once() : $this->never())
            ->method('setGet')
            ->with('language', $language);

        $_GET = [$getKey => 'foo'];

        $this->request->method('getPathInfo')->willReturn($pathInfo);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Duplicate parameter in query');

        $provider->getPageIdFromUrl($this->request);
    }

    public function getParameterExistsProvider()
    {
        return [
            ['/foo/bar/baz.html', false, 'bar'],
            ['/foo/foo/bar.html', false, 'foo'],
            ['/de/foo/foo/bar.html', 'de', 'foo'],
        ];
    }

    public function testThrowsExceptionIfAutoItemKeyIsInParameters()
    {
        $GLOBALS['TL_AUTO_ITEM'] = ['bar'];

        $provider = new RouteProvider($this->framework, $this->input, '.html', false, false, true);

        $this->input->expects($this->never())->method('setGet');

        $this->request->method('getPathInfo')->willReturn('/foo/bar/baz.html');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Request contains an auto_item keyword');

        $provider->getPageIdFromUrl($this->request);
    }

    /**
     * @dataProvider folderUrlsLookupProvider
     */
    public function testLooksUpFolderUrlsInPageModel($pathInfo, array $aliases, bool $prependLocale)
    {
        $pageAdapter = $this->createPartialMock(Adapter::class, ['findByAliases']);
        $pageAdapter
            ->expects($this->once())
            ->method('findByAliases')
            ->with($aliases)
            ->willReturn(null)
        ;

        $this->framework = $this->mockContaoFramework([PageModel::class => $pageAdapter]);

        $provider = new RouteProvider($this->framework, $this->input, '.html', $prependLocale, true, true);

        $this->request->method('getPathInfo')->willReturn($pathInfo);

        $provider->getPageIdFromUrl($this->request);
    }

    public function folderUrlsLookupProvider()
    {
        return [
            ['/foo/bar.html', ['foo/bar', 'foo'], false],
            ['/foo/bar/baz.html', ['foo/bar/baz', 'foo/bar', 'foo'], false],
            ['/de/foo/bar.html', ['foo/bar', 'foo'], true],
            ['/de/foo/bar/baz.html', ['foo/bar/baz', 'foo/bar', 'foo'], true],
        ];
    }

    /**
     * @dataProvider pageIdFromUrlProvider
     */
    public function testGetPageIdFromUrl($pathInfo, $urlSuffix, $expectedPageId, array $expectedParameters)
    {
        $provider = new RouteProvider(
            $this->framework,
            $this->input,
            $urlSuffix,
            isset($expectedParameters['language']),
            false,
            isset($expectedParameters['auto_item'])
        );

        $inputValidators = [];

        foreach ($expectedParameters as $k => $v) {
            $args = [$this->equalTo($k), $this->equalTo($v)];

            if ($k !== 'language') {
                $args[] = $this->equalTo(true);
            }

            $inputValidators[] = $args;
        }

        $this->input
            ->expects($this->exactly(\count($expectedParameters)))
            ->method('setGet')
            ->withConsecutive(...$inputValidators);

        $this->request->method('getPathInfo')->willReturn($pathInfo);

        $this->assertSame($expectedPageId, $provider->getPageIdFromUrl($this->request));
    }

    public function pageIdFromUrlProvider()
    {
        return [
            ['/foo/bar.html', '.html', 'foo', ['auto_item' => 'bar']],
            ['/foo/bar.php', '.php', 'foo', ['auto_item' => 'bar']],
            ['/foo//bar.html', '.html', 'foo', []],
            ['/de/foo/bar.html', '.html', 'foo', ['language' => 'de', 'auto_item' => 'bar']],
            ['/de-DE/foo/bar.html', '.html', 'foo', ['language' => 'de-DE', 'auto_item' => 'bar']],
            ['/foo/bar/baz.html', '.html', 'foo', ['bar' => 'baz']],
            ['/bar/foo/baz.html', '.html', 'bar', ['foo' => 'baz']],
            ['/foo/bar/bar/baz.html', '.html', 'foo', ['auto_item' => 'bar', 'bar' => 'baz']],
            ['/en/foo/bar/bar/baz.html', '.html', 'foo', ['language' => 'en', 'auto_item' => 'bar', 'bar' => 'baz']],
        ];
    }


    /**
     * @dataProvider folderUrlsProvider
     */
    public function testFolderUrls($pathInfo, string $language, array $pages, $expected)
    {
        $pageAdapter = $this->createPartialMock(Adapter::class, ['findByAliases']);
        $pageAdapter
            ->expects($this->once())
            ->method('findByAliases')
            ->willReturn(new Collection($pages, 'tl_page'))
        ;

        $this->framework = $this->mockContaoFramework([PageModel::class => $pageAdapter]);
        $this->input->method('get')->with('language')->willReturn($language);

        $provider = new RouteProvider($this->framework, $this->input, '.html', (bool) $language, true, true);

        $this->request->method('getPathInfo')->willReturn($pathInfo);
        $this->request->method('getHost')->willReturn('localhost');

        $this->assertSame($expected, $provider->getPageIdFromUrl($this->request));
    }

    public function folderUrlsProvider()
    {
        return [
            [
                '/foo/bar.html',
                '',
                [$this->mockPageModel('foo', 'de')],
                'foo',
            ],
            [
                '/foo/bar/bar.html',
                '',
                [$this->mockPageModel('foo', 'de')],
                'foo',
            ],
            [
                '/foo/bar/bar.html',
                '',
                [$this->mockPageModel('foo/bar', 'de')],
                'foo/bar',
            ],
            [
                '/foo/bar/bar.html',
                '',
                [$this->mockPageModel('foo/bar', 'de'), $this->mockPageModel('foo', 'de')],
                'foo/bar',
            ],
            [
                '/de/foo/bar/bar.html',
                'de',
                [$this->mockPageModel('foo/bar', 'en', false), $this->mockPageModel('foo', 'de')],
                'foo',
            ],
        ];
    }

    private function mockPageModel(string $alias, string $language, bool $fallback = true, string $domain = '')
    {
        $model = $this->createPartialMock(PageModel::class, ['loadDetails']);

        $model
            ->method('loadDetails')
            ->willReturnSelf();

        $model->alias = $alias;
        $model->rootLanguage = $language;
        $model->rootIsFallback = $fallback;
        $model->domain = $domain;

        return $model;
    }
}
