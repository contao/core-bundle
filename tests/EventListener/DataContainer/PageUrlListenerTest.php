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

use Contao\CoreBundle\EventListener\DataContainer\PageUrlListener;
use Contao\CoreBundle\Exception\RouteParametersException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Routing\Matcher\UrlMatcher;
use Contao\CoreBundle\Routing\Page\PageRegistry;
use Contao\CoreBundle\Routing\Page\PageRoute;
use Contao\CoreBundle\Slug\Slug;
use Contao\CoreBundle\Tests\TestCase;
use Contao\DataContainer;
use Contao\Input;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageUrlListenerTest extends TestCase
{
    /**
     * @dataProvider generatesAliasProvider
     */
    public function testGeneratesAlias(array $currentRecord, array $input, string $slugResult, string $expectedAlias): void
    {
        $page = $this->mockClassWithProperties(PageModel::class, $currentRecord);

        $pageAdapter = $this->mockAdapter(['findWithDetails']);
        $pageAdapter
            ->expects($this->once())
            ->method('findWithDetails')
            ->with($page->id)
            ->willReturn($page)
        ;

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageAdapter,
            Input::class => $this->mockInputAdapter($input),
        ]);

        $expectedTitle = $input['title'] ?? $page->title;

        $slug = $this->createMock(Slug::class);
        $slug
            ->expects($this->once())
            ->method('generate')
            ->with($expectedTitle, $page->id, $this->isType('callable'))
            ->willReturn($slugResult)
        ;

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => $page->id]);

        $listener = new PageUrlListener(
            $framework,
            $slug,
            $this->createMock(TranslatorInterface::class),
            $this->mockConnection(),
            $this->mockPageRegistry(),
            $this->mockRouter(),
            new UrlMatcher(),
        );

        $this->assertSame($expectedAlias, $listener->generateAlias('', $dc));
    }

    public function generatesAliasProvider(): \Generator
    {
        yield 'Test alias without changes and no folderUrl' => [
            [
                'id' => 17,
                'title' => 'Foo',
                'useFolderUrl' => false,
                'folderUrl' => '',
            ],
            [],
            'foo',
            'foo',
        ];

        yield 'Test alias without changes and folderUrl' => [
            [
                'id' => 17,
                'title' => 'Foo',
                'useFolderUrl' => true,
                'folderUrl' => 'bar/',
            ],
            [],
            'foo',
            'bar/foo',
        ];

        yield 'Test alias when changing the title and without folderUrl' => [
            [
                'id' => 17,
                'title' => 'Foo',
                'useFolderUrl' => false,
                'folderUrl' => '',
            ],
            [
                'title' => 'Bar',
            ],
            'bar',
            'bar',
        ];

        yield 'Test alias when changing the title and folderUrl' => [
            [
                'id' => 17,
                'title' => 'Foo',
                'useFolderUrl' => true,
                'folderUrl' => 'bar/',
            ],
            [
                'title' => 'Bar',
            ],
            'bar',
            'bar/bar',
        ];
    }

    /**
     * @dataProvider duplicateAliasProvider
     */
    public function testChecksForDuplicatesWhenGeneratingAlias(array $currentRecord, array $pages, string $value, string $generated, bool $expectExists, bool $throwParametersException = false): void
    {
        $currentPage = $this->mockClassWithProperties(PageModel::class, $currentRecord);
        $currentRoute = new PageRoute($currentPage);

        $aliasPages = [];
        $aliasRoutes = [];

        foreach ($pages as $page) {
            $aliasPage = $this->mockClassWithProperties(PageModel::class, $page);
            $aliasPages[] = $aliasPage;
            $aliasRoutes[] = new PageRoute($aliasPage);
        }

        $pageAdapter = $this->mockAdapter(['findWithDetails', 'findSimilarByAlias']);
        $pageAdapter
            ->expects($this->once())
            ->method('findWithDetails')
            ->with($currentRecord['id'])
            ->willReturn($currentPage)
        ;

        $pageAdapter
            ->expects($this->once())
            ->method('findSimilarByAlias')
            ->with($currentPage)
            ->willReturn($aliasPages)
        ;

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageAdapter,
            Input::class => $this->mockInputAdapter([]),
        ]);

        $slug = $this->createMock(Slug::class);
        $slug
            ->expects($this->once())
            ->method('generate')
            ->with(
                $currentRecord['title'],
                $currentRecord['id'],
                $this->callback(
                    function (callable $callback) use ($expectExists, $generated) {
                        $this->assertSame($expectExists, $callback($generated));

                        return true;
                    },
                ),
            )
            ->willReturn($generated)
        ;

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => $currentRecord['id']]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn($currentRecord)
        ;

        $pageRegistry = $this->mockPageRegistry(array_fill(0, \count($pages) + 1, true), [$currentRoute, ...$aliasRoutes]);

        $listener = new PageUrlListener(
            $framework,
            $slug,
            $this->createMock(TranslatorInterface::class),
            $this->mockConnection(),
            $pageRegistry,
            $this->mockRouter($throwParametersException ? false : $currentRoute),
            new UrlMatcher(),
        );

        $listener->generateAlias('', $dc);
    }

    /**
     * @dataProvider duplicateAliasProvider
     */
    public function testChecksForDuplicatesWhenValidatingAlias(array $currentRecord, array $pages, string $value, string $generated, bool $expectExists, bool $throwParametersException = false): void
    {
        $currentPage = $this->mockClassWithProperties(PageModel::class, $currentRecord);
        $currentRoute = new PageRoute($currentPage);

        $aliasPages = [];
        $aliasRoutes = [];

        foreach ($pages as $page) {
            $aliasPage = $this->mockClassWithProperties(PageModel::class, $page);
            $aliasPages[] = $aliasPage;
            $aliasRoutes[] = new PageRoute($aliasPage);
        }

        $pageAdapter = $this->mockAdapter(['findWithDetails', 'findSimilarByAlias']);
        $pageAdapter
            ->expects($this->once())
            ->method('findWithDetails')
            ->with($currentRecord['id'])
            ->willReturn($currentPage)
        ;

        $pageAdapter
            ->expects($this->once())
            ->method('findSimilarByAlias')
            ->with($currentPage)
            ->willReturn($aliasPages)
        ;

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageAdapter,
            Input::class => $this->mockInputAdapter([]),
        ]);

        $slug = $this->createMock(Slug::class);
        $slug
            ->expects($this->never())
            ->method('generate')
        ;

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => $currentRecord['id']]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn($currentRecord)
        ;

        $pageRegistry = $this->mockPageRegistry(array_fill(0, \count($pages) + 1, true), [$currentRoute, ...$aliasRoutes]);

        if ($expectExists) {
            $translator = $this->mockTranslator('ERR.pageUrlNameExists', [$pages[0]['title'], $pages[0]['id']]);
        } else {
            $translator = $this->mockTranslator();
        }

        $listener = new PageUrlListener(
            $framework,
            $slug,
            $translator,
            $this->mockConnection(),
            $pageRegistry,
            $this->mockRouter($throwParametersException ? false : $currentRoute),
            new UrlMatcher(),
        );

        $listener->generateAlias($value, $dc);
    }

    /**
     * @dataProvider duplicateAliasProvider
     */
    public function testDoesNotCheckAliasIfCurrentPageIsUnrouteable(array $currentRecord, array $pages, string $value): void
    {
        $currentPage = $this->mockClassWithProperties(PageModel::class, $currentRecord);

        $pageAdapter = $this->mockAdapter(['findWithDetails', 'findSimilarByAlias']);
        $pageAdapter
            ->expects($this->once())
            ->method('findWithDetails')
            ->with($currentRecord['id'])
            ->willReturn($currentPage)
        ;

        $pageAdapter
            ->expects($this->never())
            ->method('findSimilarByAlias')
        ;

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageAdapter,
            Input::class => $this->mockInputAdapter([]),
        ]);

        $slug = $this->createMock(Slug::class);
        $slug
            ->expects($this->never())
            ->method('generate')
        ;

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => $currentRecord['id']]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn($currentRecord)
        ;

        $listener = new PageUrlListener(
            $framework,
            $slug,
            $this->createMock(TranslatorInterface::class),
            $this->createMock(Connection::class),
            $this->mockPageRegistry([false]),
            $this->mockRouter(),
            new UrlMatcher(),
        );

        $this->assertSame($value, $listener->generateAlias($value, $dc));
    }

    /**
     * @dataProvider duplicateAliasProvider
     */
    public function testDoesNotCheckAliasIfAliasPageIsUnrouteable(array $currentRecord, array $pages, string $value): void
    {
        $currentPage = $this->mockClassWithProperties(PageModel::class, $currentRecord);
        $currentRoute = new PageRoute($currentPage);

        $aliasPages = [];
        $aliasRoutes = [];

        foreach ($pages as $page) {
            $aliasPage = $this->mockClassWithProperties(PageModel::class, $page);
            $aliasPages[] = $aliasPage;
            $aliasRoutes[] = new PageRoute($aliasPage);
        }

        $pageAdapter = $this->mockAdapter(['findWithDetails', 'findSimilarByAlias']);
        $pageAdapter
            ->expects($this->once())
            ->method('findWithDetails')
            ->with($currentRecord['id'])
            ->willReturn($currentPage)
        ;

        $pageAdapter
            ->expects($this->once())
            ->method('findSimilarByAlias')
            ->with($currentPage)
            ->willReturn($aliasPages)
        ;

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageAdapter,
            Input::class => $this->mockInputAdapter([]),
        ]);

        $slug = $this->createMock(Slug::class);
        $slug
            ->expects($this->never())
            ->method('generate')
        ;

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => $currentRecord['id']]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn($currentRecord)
        ;

        $pageRegistry = $this->mockPageRegistry([...[true], ...array_fill(0, \count($pages), false)], [$currentRoute, ...$aliasRoutes]);

        $listener = new PageUrlListener(
            $framework,
            $slug,
            $this->createMock(TranslatorInterface::class),
            $this->createMock(Connection::class),
            $pageRegistry,
            $this->mockRouter($currentRoute),
            new UrlMatcher(),
        );

        $this->assertSame($value, $listener->generateAlias($value, $dc));
    }

    public function duplicateAliasProvider(): \Generator
    {
        yield 'No duplicate aliases' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
            ],
            [],
            'foo',
            'foo',
            false,
        ];

        yield 'in same root' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
            ]],
            'foo',
            'foo',
            true,
        ];

        yield 'in same root with prefix' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'de',
                'urlSuffix' => '',
                'rootLanguage' => 'de',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'de',
                'urlSuffix' => '',
                'rootLanguage' => 'de',
            ]],
            'foo',
            'foo',
            true,
        ];

        yield 'in same root with suffix' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ]],
            'foo',
            'foo',
            true,
        ];

        yield 'in same root with prefix and suffix' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'de',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'de',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ]],
            'foo',
            'foo',
            true,
        ];

        yield 'in separate root without language prefix' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 2,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
            ]],
            'foo',
            'foo',
            true,
        ];

        yield 'in separate root without language prefix and requireItem' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
                'requireItem' => true,
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 2,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
                'requireItem' => true,
            ]],
            'foo',
            'foo',
            true,
            true,
        ];

        yield 'in separate root with language prefix' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'de',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 2,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'fr',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
            ]],
            'foo',
            'foo',
            false,
        ];

        yield 'in separate domain' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 2,
                'useFolderUrl' => false,
                'domain' => 'example.com',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
            ]],
            'foo',
            'foo',
            false,
        ];

        yield 'in separate domain with requireItem' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
                'requireItem' => true,
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 2,
                'useFolderUrl' => false,
                'domain' => 'example.com',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
                'requireItem' => true,
            ]],
            'foo',
            'foo',
            false,
            true,
        ];

        yield 'with separate url suffix' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 2,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
            ]],
            'foo',
            'foo',
            false,
        ];

        yield 'with same prefix but separate url suffix' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'de',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 2,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'de',
                'urlSuffix' => '',
                'rootLanguage' => 'en',
            ]],
            'foo',
            'foo',
            false,
        ];

        yield 'with prefix fragment' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'de/ch',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'ch/foo',
                'rootId' => 2,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'de',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ]],
            'foo',
            'foo',
            true,
        ];

        yield 'with prefix fragment inverted' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'ch/foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'de',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 2,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => 'de/ch',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ]],
            'ch/foo',
            'ch/foo',
            true,
        ];

        yield 'with suffix fragment' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo.ht',
                'rootId' => 2,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => 'ml',
                'rootLanguage' => 'en',
            ]],
            'foo',
            'foo',
            true,
        ];

        yield 'with suffix fragment inverted' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'foo.ht',
                'rootId' => 1,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => 'ml',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'foo',
                'rootId' => 2,
                'useFolderUrl' => false,
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ]],
            'foo.ht',
            'foo.ht',
            true,
        ];

        yield 'with folderUrl match' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'bar/foo',
                'rootId' => 1,
                'useFolderUrl' => true,
                'folderUrl' => 'bar/',
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'bar/foo',
                'rootId' => 2,
                'useFolderUrl' => true,
                'folderUrl' => '',
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ]],
            'bar/foo',
            'foo',
            true,
        ];

        yield 'with folderUrl mismatch' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'baz/foo',
                'rootId' => 1,
                'useFolderUrl' => true,
                'folderUrl' => 'baz/',
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'bar/foo',
                'rootId' => 2,
                'useFolderUrl' => true,
                'folderUrl' => '',
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ]],
            'baz/foo',
            'foo',
            false,
        ];

        yield 'everything' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'bar/foo',
                'rootId' => 1,
                'useFolderUrl' => true,
                'folderUrl' => 'bar/',
                'domain' => '',
                'urlPrefix' => 'ch/de',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'de/bar/foo.ht',
                'rootId' => 2,
                'useFolderUrl' => true,
                'folderUrl' => '',
                'domain' => '',
                'urlPrefix' => 'ch',
                'urlSuffix' => 'ml',
                'rootLanguage' => 'en',
            ]],
            'bar/foo',
            'foo',
            true,
        ];

        yield 'ignores alias page not found' => [
            [
                'id' => 1,
                'title' => 'Foo',
                'alias' => 'baz/foo',
                'rootId' => 1,
                'useFolderUrl' => true,
                'folderUrl' => 'baz/',
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ],
            [[
                'id' => 2,
                'title' => 'Foo',
                'alias' => 'bar/foo',
                'rootId' => 2,
                'useFolderUrl' => true,
                'folderUrl' => '',
                'domain' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => 'en',
            ]],
            'baz/foo',
            'foo',
            false,
        ];
    }

    public function testPreventsNumericAliases(): void
    {
        $page = $this->mockClassWithProperties(PageModel::class, ['id' => 17]);

        $pageAdapter = $this->mockAdapter(['findWithDetails']);
        $pageAdapter
            ->expects($this->once())
            ->method('findWithDetails')
            ->with($page->id)
            ->willReturn($page)
        ;

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageAdapter,
            Input::class => $this->mockInputAdapter([]),
        ]);

        $slug = $this->createMock(Slug::class);
        $slug
            ->expects($this->never())
            ->method('generate')
        ;

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('ERR.aliasNumeric')
            ->willReturn('Numeric aliases are not supported!')
        ;

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => $page->id]);

        $listener = new PageUrlListener(
            $framework,
            $slug,
            $translator,
            $this->createMock(Connection::class),
            $this->mockPageRegistry(),
            $this->mockRouter(),
            new UrlMatcher(),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Numeric aliases are not supported!');

        $listener->generateAlias('123', $dc);
    }

    public function testReturnsValueWhenValidatingUrlPrefix(): void
    {
        $inputData = [
            'dns' => '',
            'urlPrefix' => 'de',
            'urlSuffix' => '.html',
        ];

        $pageModels = $this->getPageModels([
            [
                'id' => 1,
                'pid' => 0,
                'type' => 'root',
                'alias' => 'root',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 2,
                'pid' => 1,
                'alias' => 'foo',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
        ]);

        $pageAdapter = $this->mockAdapter(['findWithDetails', 'findByPid', 'findSimilarByAlias']);
        $pageAdapter
            ->method('findWithDetails')
            ->willReturnCallback(static fn (int $id) => $pageModels['id'][$id] ?? null)
        ;

        $pageAdapter
            ->method('findByPid')
            ->willReturnCallback(static fn (int $pid) => $pageModels['pid'][$pid] ?? null)
        ;

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageAdapter,
            Input::class => $this->mockInputAdapter($inputData),
        ]);

        $route = new PageRoute($pageModels['id'][2]);
        $pageRegistry = $this->mockPageRegistry([true, true], [$route]);

        $listener = new PageUrlListener(
            $framework,
            $this->createMock(Slug::class),
            $this->mockTranslator(),
            $this->mockConnection(true),
            $pageRegistry,
            $this->mockRouter($route),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn(['type' => 'root', 'dns' => '', 'urlPrefix' => 'de', 'urlSuffix' => ''])
        ;

        $listener->validateUrlPrefix('en', $dc);
    }

    public function testThrowsExceptionOnDuplicateUrlPrefixInDomain(): void
    {
        $translator = $this->mockTranslator('ERR.urlPrefixExists', ['en']);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with(
                "SELECT COUNT(*) FROM tl_page WHERE urlPrefix=:urlPrefix AND dns=:dns AND id!=:rootId AND type='root'",
                ['urlPrefix' => 'en', 'dns' => 'www.example.com', 'rootId' => 1],
            )
            ->willReturn(1)
        ;

        $listener = new PageUrlListener(
            $this->mockContaoFramework(),
            $this->createMock(Slug::class),
            $translator,
            $connection,
            $this->mockPageRegistry(),
            $this->mockRouter(),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn([
                'type' => 'root',
                'dns' => 'www.example.com',
                'urlPrefix' => 'de',
                'urlSuffix' => '',
            ])
        ;

        $listener->validateUrlPrefix('en', $dc);
    }

    public function testThrowsExceptionIfUrlPrefixLeadsToDuplicatePages(): void
    {
        $inputData = [
            'dns' => '',
            'urlPrefix' => 'de',
            'urlSuffix' => '.html',
            'rootLanguage' => 'de',
        ];

        $pageModels = $this->getPageModels([
            [
                'id' => 1,
                'pid' => 0,
                'type' => 'root',
                'alias' => 'root',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 2,
                'pid' => 1,
                'alias' => 'foo',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 3,
                'pid' => 1,
                'alias' => 'bar',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 4,
                'pid' => 3,
                'alias' => 'bar/foo',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 5,
                'pid' => 0,
                'type' => 'root',
                'urlPrefix' => 'de',
                'urlSuffix' => '.html',
                'rootLanguage' => 'de',
            ],
            [
                'id' => 6,
                'pid' => 5,
                'alias' => 'bar/foo',
                'urlPrefix' => 'de',
                'urlSuffix' => '.html',
                'rootLanguage' => 'de',
            ],
        ]);

        $pageAdapter = $this->mockAdapter(['findWithDetails', 'findByPid', 'findSimilarByAlias']);
        $pageAdapter
            ->method('findWithDetails')
            ->willReturnCallback(static fn (int $id) => $pageModels['id'][$id] ?? null)
        ;

        $pageAdapter
            ->method('findByPid')
            ->willReturnCallback(static fn (int $pid) => $pageModels['pid'][$pid] ?? null)
        ;

        $pageAdapter
            ->method('findSimilarByAlias')
            ->willReturn(null, null, [$pageModels['id'][6]])
        ;

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageAdapter,
            Input::class => $this->mockInputAdapter($inputData),
        ]);

        // Expects exception
        $translator = $this->mockTranslator('ERR.pageUrlPrefix', ['/de/bar/foo.html']);

        $listener = new PageUrlListener(
            $framework,
            $this->createMock(Slug::class),
            $translator,
            $this->mockConnection(true),
            new PageRegistry($this->createMock(Connection::class)),
            $this->mockRouter(3),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn(['type' => 'root', 'dns' => '', 'urlPrefix' => 'de', 'urlSuffix' => ''])
        ;

        $listener->validateUrlPrefix('en', $dc);
    }

    public function testIgnoresPagesWithoutAliasWhenValidatingUrlPrefix(): void
    {
        $inputData = [
            'dns' => '',
            'urlPrefix' => 'de',
            'urlSuffix' => '.html',
        ];

        $pageModels = $this->getPageModels([
            [
                'id' => 1,
                'pid' => 0,
                'type' => 'root',
                'alias' => 'root',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 2,
                'pid' => 1,
                'alias' => 'foo',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 3,
                'pid' => 1,
                'alias' => '',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 4,
                'pid' => 3,
                'alias' => 'foo/bar',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 5,
                'pid' => 0,
                'type' => 'root',
                'urlPrefix' => 'de',
                'urlSuffix' => '.html',
                'rootLanguage' => 'de',
            ],
            [
                'id' => 6,
                'pid' => 5,
                'alias' => '',
                'urlPrefix' => 'de',
                'urlSuffix' => '.html',
                'rootLanguage' => 'de',
            ],
        ]);

        $pageAdapter = $this->mockAdapter(['findWithDetails', 'findByPid', 'findSimilarByAlias']);
        $pageAdapter
            ->method('findWithDetails')
            ->willReturnCallback(static fn (int $id) => $pageModels['id'][$id] ?? null)
        ;

        $pageAdapter
            ->method('findByPid')
            ->willReturnCallback(static fn (int $pid) => $pageModels['pid'][$pid] ?? null)
        ;

        $pageAdapter
            ->method('findSimilarByAlias')
            ->willReturn(null, null, [$pageModels['id'][4]])
        ;

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageAdapter,
            Input::class => $this->mockInputAdapter($inputData),
        ]);

        $listener = new PageUrlListener(
            $framework,
            $this->createMock(Slug::class),
            $this->createMock(TranslatorInterface::class),
            $this->mockConnection(true),
            new PageRegistry($this->createMock(Connection::class)),
            $this->mockRouter(2),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn(['type' => 'root', 'dns' => '', 'urlPrefix' => 'de', 'urlSuffix' => ''])
        ;

        $listener->validateUrlPrefix('en', $dc);
    }

    public function testDoesNotValidateTheUrlPrefixIfPageTypeIsNotRoot(): void
    {
        $pageAdapter = $this->mockAdapter(['findByPk']);
        $pageAdapter
            ->expects($this->never())
            ->method('findByPk')
        ;

        $framework = $this->mockContaoFramework([PageModel::class => $pageAdapter]);

        $listener = new PageUrlListener(
            $framework,
            $this->createMock(Slug::class),
            $this->createMock(TranslatorInterface::class),
            $this->mockConnectionWithStatement(),
            $this->mockPageRegistry(),
            $this->mockRouter(),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn([
                'type' => 'regular',
                'urlPrefix' => 'en',
            ])
        ;

        $listener->validateUrlPrefix('de/ch', $dc);
    }

    public function testDoesNotValidateTheUrlPrefixIfTheValueHasNotChanged(): void
    {
        $pageAdapter = $this->mockAdapter(['findByPk']);
        $pageAdapter
            ->expects($this->never())
            ->method('findByPk')
        ;

        $framework = $this->mockContaoFramework([PageModel::class => $pageAdapter]);

        $listener = new PageUrlListener(
            $framework,
            $this->createMock(Slug::class),
            $this->createMock(TranslatorInterface::class),
            $this->mockConnectionWithStatement(),
            $this->mockPageRegistry(),
            $this->mockRouter(),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn([
                'type' => 'root',
                'urlPrefix' => 'de/ch',
            ])
        ;

        $listener->validateUrlPrefix('de/ch', $dc);
    }

    public function testDoesNotValidateTheUrlPrefixIfTheRootPageIsNotFound(): void
    {
        $pageAdapter = $this->mockAdapter(['findWithDetails']);
        $pageAdapter
            ->expects($this->once())
            ->method('findWithDetails')
            ->with(1)
            ->willReturn(null)
        ;

        $framework = $this->mockContaoFramework([PageModel::class => $pageAdapter]);

        $listener = new PageUrlListener(
            $framework,
            $this->createMock(Slug::class),
            $this->createMock(TranslatorInterface::class),
            $this->mockConnectionWithStatement(),
            $this->mockPageRegistry(),
            $this->mockRouter(),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn([
                'type' => 'root',
                'dns' => '',
                'urlPrefix' => 'en',
            ])
        ;

        $listener->validateUrlPrefix('de/ch', $dc);
    }

    public function testReturnsValueWhenValidatingUrlSuffix(): void
    {
        $inputData = [
            'dns' => '',
            'urlPrefix' => 'de',
            'urlSuffix' => '.html',
        ];

        $pageModels = $this->getPageModels([
            [
                'id' => 1,
                'pid' => 0,
                'type' => 'root',
                'alias' => 'root',
                'urlPrefix' => '',
                'urlSuffix' => '',
                'rootLanguage' => '',
            ],
            [
                'id' => 2,
                'pid' => 1,
                'alias' => 'foo',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
        ]);

        $pageAdapter = $this->mockAdapter(['findWithDetails', 'findByPid', 'findSimilarByAlias']);
        $pageAdapter
            ->method('findWithDetails')
            ->willReturnCallback(static fn (int $id) => $pageModels['id'][$id] ?? null)
        ;

        $pageAdapter
            ->method('findByPid')
            ->willReturnCallback(static fn (int $pid) => $pageModels['pid'][$pid] ?? null)
        ;

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageAdapter,
            Input::class => $this->mockInputAdapter($inputData),
        ]);

        $listener = new PageUrlListener(
            $framework,
            $this->createMock(Slug::class),
            $this->mockTranslator(),
            $this->mockConnection(),
            new PageRegistry($this->createMock(Connection::class)),
            $this->mockRouter(1),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn(['type' => 'root', 'urlPrefix' => 'de', 'urlSuffix' => ''])
        ;

        $this->assertSame('.html', $listener->validateUrlSuffix('.html', $dc));
    }

    public function testThrowsExceptionOnDuplicateUrlSuffix(): void
    {
        $inputData = [
            'dns' => '',
            'urlPrefix' => 'de',
            'urlSuffix' => '.html',
        ];

        $pageModels = $this->getPageModels([
            [
                'id' => 1,
                'pid' => 0,
                'type' => 'root',
                'alias' => 'root',
                'urlPrefix' => '',
                'urlSuffix' => '',
            ],
            [
                'id' => 2,
                'pid' => 1,
                'alias' => 'foo',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 3,
                'pid' => 1,
                'alias' => 'bar',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 4,
                'pid' => 3,
                'alias' => 'bar/foo',
                'urlPrefix' => '',
                'urlSuffix' => '.html',
                'rootLanguage' => '',
            ],
            [
                'id' => 5,
                'pid' => 0,
                'type' => 'root',
                'urlPrefix' => 'de',
                'urlSuffix' => '.html',
                'rootLanguage' => 'de',
            ],
            [
                'id' => 6,
                'pid' => 5,
                'alias' => 'bar/foo',
                'urlPrefix' => 'de',
                'urlSuffix' => '.html',
            ],
        ]);

        $pageAdapter = $this->mockAdapter(['findWithDetails', 'findByPid', 'findSimilarByAlias']);
        $pageAdapter
            ->method('findWithDetails')
            ->willReturnCallback(static fn (int $id) => $pageModels['id'][$id] ?? null)
        ;

        $pageAdapter
            ->method('findByPid')
            ->willReturnCallback(static fn (int $pid) => $pageModels['pid'][$pid] ?? null)
        ;

        $pageAdapter
            ->method('findSimilarByAlias')
            ->willReturn(null, null, [$pageModels['id'][4]])
        ;

        $framework = $this->mockContaoFramework([
            PageModel::class => $pageAdapter,
            Input::class => $this->mockInputAdapter($inputData),
        ]);

        $translator = $this->mockTranslator('ERR.pageUrlSuffix', ['/de/bar/foo.html']);

        $listener = new PageUrlListener(
            $framework,
            $this->createMock(Slug::class),
            $translator,
            $this->mockConnection(),
            new PageRegistry($this->createMock(Connection::class)),
            $this->mockRouter(3),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn(['type' => 'root', 'urlPrefix' => 'de', 'urlSuffix' => ''])
        ;

        $listener->validateUrlSuffix('.html', $dc);
    }

    public function testDoesNotValidateTheUrlSuffixIfPageTypeIsNotRoot(): void
    {
        $pageAdapter = $this->mockAdapter(['findByPk']);
        $pageAdapter
            ->expects($this->never())
            ->method('findByPk')
        ;

        $framework = $this->mockContaoFramework([PageModel::class => $pageAdapter]);

        $listener = new PageUrlListener(
            $framework,
            $this->createMock(Slug::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(Connection::class),
            $this->mockPageRegistry(),
            $this->mockRouter(),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn([
                'type' => 'regular',
                'urlSuffix' => '/',
            ])
        ;

        $listener->validateUrlPrefix('.html', $dc);
    }

    public function testDoesNotValidateTheUrlSuffixIfTheValueHasNotChanged(): void
    {
        $pageAdapter = $this->mockAdapter(['findByPk']);
        $pageAdapter
            ->expects($this->never())
            ->method('findByPk')
        ;

        $framework = $this->mockContaoFramework([PageModel::class => $pageAdapter]);

        $listener = new PageUrlListener(
            $framework,
            $this->createMock(Slug::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(Connection::class),
            $this->mockPageRegistry(),
            $this->mockRouter(),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn([
                'type' => 'root',
                'urlPrefix' => '.html',
            ])
        ;

        $listener->validateUrlPrefix('.html', $dc);
    }

    public function testDoesNotValidateTheUrlSuffixIfTheRootPageIsNotFound(): void
    {
        $pageAdapter = $this->mockAdapter(['findWithDetails']);
        $pageAdapter
            ->expects($this->once())
            ->method('findWithDetails')
            ->with(1)
            ->willReturn(null)
        ;

        $framework = $this->mockContaoFramework([PageModel::class => $pageAdapter]);

        $listener = new PageUrlListener(
            $framework,
            $this->createMock(Slug::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(Connection::class),
            $this->mockPageRegistry(),
            $this->mockRouter(),
            new UrlMatcher(),
        );

        $dc = $this->mockClassWithProperties(DataContainer::class, ['id' => 1]);
        $dc
            ->method('getCurrentRecord')
            ->willReturn([
                'type' => 'root',
                'dns' => '',
                'urlPrefix' => '',
            ])
        ;

        $listener->validateUrlPrefix('.html', $dc);
    }

    /**
     * @return array{id: array<PageModel&MockObject>, pid: array<int, array<PageModel&MockObject>>}
     */
    private function getPageModels(array $pages): array
    {
        $return = ['id' => [], 'pid' => []];

        foreach ($pages as $row) {
            $page = $this->mockClassWithProperties(PageModel::class, $row);

            $return['id'][$row['id']] = $page;

            if (isset($row['pid'])) {
                $return['pid'][$row['pid']][] = $page;
            }
        }

        return $return;
    }

    private function mockConnection(bool $prefixCheck = false): Connection&MockObject
    {
        $connection = $this->createMock(Connection::class);

        if ($prefixCheck) {
            $connection
                ->expects($this->once())
                ->method('fetchOne')
                ->with("SELECT COUNT(*) FROM tl_page WHERE urlPrefix=:urlPrefix AND dns=:dns AND id!=:rootId AND type='root'")
                ->willReturn(0)
            ;
        }

        return $connection;
    }

    /**
     * @return Adapter<Input>&MockObject
     */
    private function mockInputAdapter(array $inputData): Adapter&MockObject
    {
        $inputAdapter = $this->mockAdapter(['post']);
        $inputAdapter
            ->method('post')
            ->willReturnCallback(static fn ($key) => $inputData[$key] ?? null)
        ;

        return $inputAdapter;
    }

    private function mockTranslator(string|null $messageKey = null, array $arguments = []): TranslatorInterface&MockObject
    {
        $translator = $this->createMock(TranslatorInterface::class);

        if (null === $messageKey) {
            $translator
                ->expects($this->never())
                ->method('trans')
            ;

            return $translator;
        }

        $translator
            ->expects($this->once())
            ->method('trans')
            ->with($messageKey, $arguments, 'contao_default')
            ->willReturn($messageKey)
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($messageKey);

        return $translator;
    }

    private function mockConnectionWithStatement(): Connection&MockObject
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->method('fetchOne')
            ->willReturn(0)
        ;

        return $connection;
    }

    private function mockPageRegistry(array $isRoutable = [true], array $routes = []): PageRegistry&MockObject
    {
        $pageRegistry = $this->createMock(PageRegistry::class);
        $pageRegistry
            ->method('isRoutable')
            ->willReturn(...$isRoutable)
        ;

        $pageRegistry
            ->method('getRoute')
            ->willReturnOnConsecutiveCalls(...$routes)
        ;

        return $pageRegistry;
    }

    private function mockRouter(PageRoute|int|false|null $route = null): RouterInterface&MockObject
    {
        $router = $this->createMock(RouterInterface::class);

        if (null === $route) {
            $router
                ->expects($this->never())
                ->method('generate')
            ;
        } elseif (false === $route) {
            $router
                ->expects($this->atLeastOnce())
                ->method('generate')
                ->willThrowException($this->createMock(RouteParametersException::class))
            ;
        } elseif ($route instanceof PageRoute) {
            $path = '/'.$route->getPageModel()->alias;

            if ('' !== $route->getUrlPrefix()) {
                $path = '/'.$route->getUrlPrefix().$path;
            }

            $path .= $route->getUrlSuffix();

            $router
                ->expects($this->atLeastOnce())
                ->method('generate')
                ->with(
                    PageRoute::PAGE_BASED_ROUTE_NAME,
                    [RouteObjectInterface::ROUTE_OBJECT => $route],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                )
                ->willReturn($path)
            ;
        } else {
            $router
                ->expects($this->exactly($route))
                ->method('generate')
                ->with(
                    PageRoute::PAGE_BASED_ROUTE_NAME,
                    $this->isType('array'),
                    UrlGeneratorInterface::ABSOLUTE_URL,
                )
                ->willReturnCallback(
                    function (string $routeName, array $params) {
                        $this->assertArrayHasKey(RouteObjectInterface::ROUTE_OBJECT, $params);
                        $this->assertInstanceOf(PageRoute::class, $params[RouteObjectInterface::ROUTE_OBJECT]);

                        $route = $params[RouteObjectInterface::ROUTE_OBJECT];
                        $path = '/'.$route->getPageModel()->alias;

                        if ('' !== $route->getUrlPrefix()) {
                            $path = '/'.$route->getUrlPrefix().$path;
                        }

                        return $path.$route->getUrlSuffix();
                    },
                )
            ;
        }

        return $router;
    }
}
