<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Controller;

use Contao\ArticleModel;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\SitemapEvent;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Routing\Page\PageRegistry;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
#[Route('/sitemap.xml', defaults: ['_scope' => 'frontend'])]
class SitemapController extends AbstractController
{
    public function __construct(
        private readonly PageRegistry $pageRegistry,
        private readonly PageFinder $pageFinder,
        private readonly ContentUrlGenerator $urlGenerator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $rootPages = $this->pageFinder->findRootPagesForHost($request->getHost());

        if (!$rootPages) {
            throw $this->createNotFoundException();
        }

        $urls = [];
        $rootPageIds = [];
        $tags = ['contao.sitemap'];

        foreach ($rootPages as $rootPage) {
            $urls = [...$urls, ...$this->getPageAndArticleUrls($rootPage->id)];

            $rootPageIds[] = $rootPage->id;
            $tags[] = 'contao.sitemap.'.$rootPage->id;
        }

        $urls = array_unique($urls);

        $sitemap = new \DOMDocument('1.0', 'UTF-8');
        $sitemap->formatOutput = true;
        $urlSet = $sitemap->createElementNS('https://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');

        foreach ($urls as $url) {
            $loc = $sitemap->createElementNS($urlSet->namespaceURI, 'loc');
            $loc->appendChild($sitemap->createTextNode($url));

            $urlEl = $sitemap->createElementNS($urlSet->namespaceURI, 'url');
            $urlEl->appendChild($loc);
            $urlSet->appendChild($urlEl);
        }

        $sitemap->appendChild($urlSet);

        $this->container
            ->get('event_dispatcher')
            ->dispatch(new SitemapEvent($sitemap, $request, $rootPageIds), ContaoCoreEvents::SITEMAP)
        ;

        // Cache the response for a month in the shared cache and tag it for
        // invalidation purposes
        $response = new Response((string) $sitemap->saveXML(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
        $response->setSharedMaxAge(2592000); // will be unset by the MakeResponsePrivateListener if a user is logged in

        // Make sure an authorized request does not retrieve the sitemap from the HTTP
        // cache (see #6832)
        $response->setVary('Cookie');

        $this->tagResponse($tags);

        return $response;
    }

    private function getPageAndArticleUrls(int $parentPageId): array
    {
        $pageModelAdapter = $this->getContaoAdapter(PageModel::class);

        // Since the publication status of a page is not inherited by its child pages, we
        // have to use findByPid() instead of findPublishedByPid() and filter out
        // unpublished pages in the foreach loop (see #2217)
        $pageModels = $pageModelAdapter->findByPid($parentPageId, ['order' => 'sorting']);

        if (null === $pageModels) {
            return [];
        }

        $articleModelAdapter = $this->getContaoAdapter(ArticleModel::class);

        $result = [];

        // Recursively walk through all subpages
        foreach ($pageModels as $pageModel) {
            // Load details in order to inherit permission settings (see #5556)
            $pageModel->loadDetails();

            if ($pageModel->protected && !$this->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, $pageModel->groups)) {
                continue;
            }

            $isPublished = $pageModel->published && (!$pageModel->start || $pageModel->start <= time()) && (!$pageModel->stop || $pageModel->stop > time());

            if (
                $isPublished
                && !$pageModel->requireItem
                && 'noindex,nofollow' !== $pageModel->robots
                && $this->pageRegistry->supportsContentComposition($pageModel)
                && $this->pageRegistry->isRoutable($pageModel)
                && 'html' === $this->pageRegistry->getRoute($pageModel)->getDefault('_format')
            ) {
                try {
                    $urls = [$this->urlGenerator->generate($pageModel, [], UrlGeneratorInterface::ABSOLUTE_URL)];

                    // Get articles with teaser
                    if ($articleModels = $articleModelAdapter->findPublishedWithTeaserByPid($pageModel->id, ['ignoreFePreview' => true])) {
                        foreach ($articleModels as $articleModel) {
                            $urls[] = $this->urlGenerator->generate($articleModel, [], UrlGeneratorInterface::ABSOLUTE_URL);
                        }
                    }

                    $result[] = $urls;
                } catch (ExceptionInterface) {
                    // Skip URL for this page but generate child pages
                }
            }

            $result[] = $this->getPageAndArticleUrls((int) $pageModel->id);
        }

        return array_merge(...$result);
    }
}
