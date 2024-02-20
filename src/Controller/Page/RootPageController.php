<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Controller\Page;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsPage;
use Contao\CoreBundle\Exception\NoActivePageFoundException;
use Contao\PageModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
#[AsPage(contentComposition: false)]
class RootPageController extends AbstractController
{
    public function __construct(private readonly LoggerInterface|null $logger = null)
    {
    }

    public function __invoke(PageModel $pageModel): Response
    {
        $nextPage = $this->getNextPage($pageModel->id);

        return $this->redirect($this->generateContentUrl($nextPage, [], UrlGeneratorInterface::ABSOLUTE_URL));
    }

    private function getNextPage(int $rootPageId): PageModel
    {
        if ($nextPage = $this->getContaoAdapter(PageModel::class)->findFirstPublishedByPid($rootPageId)) {
            return $nextPage;
        }

        $this->logger?->error(sprintf('No active page found under root page "%s"', $rootPageId));

        throw new NoActivePageFoundException('No active page found under root page.');
    }
}
