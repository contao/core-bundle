<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\Page\PageRegistry;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Image;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentCompositionListener
{
    private ContaoFramework $framework;
    private Security $security;
    private PageRegistry $pageRegistry;
    private TranslatorInterface $translator;
    private Connection $connection;
    private RequestStack $requestStack;

    /**
     * @var Adapter<Image>
     */
    private Adapter $image;

    /**
     * @var Adapter<Backend>
     */
    private Adapter $backend;

    public function __construct(ContaoFramework $framework, Security $security, PageRegistry $pageRegistry, TranslatorInterface $translator, Connection $connection, RequestStack $requestStack)
    {
        $this->framework = $framework;
        $this->security = $security;
        $this->pageRegistry = $pageRegistry;
        $this->translator = $translator;
        $this->connection = $connection;
        $this->requestStack = $requestStack;
        $this->image = $this->framework->getAdapter(Image::class);
        $this->backend = $this->framework->getAdapter(Backend::class);
    }

    /**
     * @Callback(table="tl_page", target="list.operations.articles.button")
     */
    public function renderPageArticlesOperation(array $row, ?string $href, string $label, string $title, ?string $icon): string
    {
        if ((null === $href && null === $icon) || !$this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'article')) {
            return '';
        }

        $pageModel = $this->framework->createInstance(PageModel::class);
        $pageModel->preventSaving(false);
        $pageModel->setRow($row);

        if (!$this->pageRegistry->supportsContentComposition($pageModel) || !$this->hasArticlesInLayout($pageModel)) {
            return null !== $icon ? $this->image->getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ' : '';
        }

        return sprintf(
            '<a href="%s" title="%s">%s</a> ',
            $this->backend->addToUrl($href.'&amp;pn='.$row['id']),
            StringUtil::specialchars($title),
            $this->image->getHtml($icon, $label)
        );
    }

    /**
     * Automatically creates an article in the main column of a new page.
     *
     * @Callback(table="tl_page", target="config.onsubmit", priority=-16)
     */
    public function generateArticleForPage(DataContainer $dc): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->security->getUser();

        // Return if there is no active record (override all)
        if (!$dc->activeRecord || null === $request || !$user instanceof BackendUser || !$request->hasSession()) {
            return;
        }

        $pageModel = $this->framework->createInstance(PageModel::class);
        $pageModel->preventSaving(false);
        $pageModel->setRow($dc->activeRecord instanceof Result ? $dc->activeRecord->row() : (array) $dc->activeRecord);

        if (
            empty($pageModel->title)
            || !$this->pageRegistry->supportsContentComposition($pageModel)
            || null === ($column = $this->getArticleColumnInLayout($pageModel))
        ) {
            return;
        }

        $sessionBag = $request->getSession()->getBag('contao_backend');

        if (!$sessionBag instanceof AttributeBagInterface) {
            return;
        }

        $new_records = $sessionBag->get('new_records');

        // Not a new page
        if (!$new_records || !\is_array($new_records[$dc->table] ?? null) || !\in_array($dc->id, $new_records[$dc->table], true)) {
            return;
        }

        // Check whether there are articles (e.g. on copied pages)
        $total = $this->connection->fetchOne('SELECT COUNT(*) FROM tl_article WHERE pid=:pid', ['pid' => $dc->id]);

        if ($total > 0) {
            return;
        }

        // Create article
        $article = [
            'pid' => $dc->id,
            'sorting' => 128,
            'tstamp' => time(),
            'author' => $user->id,
            'inColumn' => $column,
            'title' => $dc->activeRecord->title,
            'alias' => str_replace('/', '-', $dc->activeRecord->alias), // see #516
            'published' => $dc->activeRecord->published,
        ];

        $this->connection->insert('tl_article', $article);
    }

    /**
     * @Callback(table="tl_article", target="list.sorting.paste_button")
     */
    public function renderArticlePasteButton(DataContainer $dc, array $row, string $table, bool $cr, ?array $clipboard = null): string
    {
        if ($table === ($GLOBALS['TL_DCA'][$dc->table]['config']['ptable'] ?? null)) {
            return $this->renderArticlePasteIntoButton($dc, $row, $cr, $clipboard);
        }

        return $this->renderArticlePasteAfterButton($dc, $row, $cr, $clipboard);
    }

    private function renderArticlePasteIntoButton(DataContainer $dc, array $row, bool $cr, ?array $clipboard = null): string
    {
        $pageModel = $this->framework->createInstance(PageModel::class);
        $pageModel->preventSaving(false);
        $pageModel->setRow($row);

        // Do not show paste button for pages without content composition or articles in layout
        if (!$this->pageRegistry->supportsContentComposition($pageModel) || !$this->hasArticlesInLayout($pageModel)) {
            return '';
        }

        if ($cr || !$this->security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_ARTICLE_HIERARCHY, $row)) {
            return $this->image->getHtml('pasteinto_.svg').' ';
        }

        return sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
            $this->backend->addToUrl('act='.$clipboard['mode'].'&amp;mode=2&amp;pid='.$row['id'].(!\is_array($clipboard['id'] ?? null) ? '&amp;id='.$clipboard['id'] : '')),
            StringUtil::specialchars($this->translator->trans($dc->table.'.pasteinto.1', [$row['id']], 'contao_'.$dc->table)),
            $this->image->getHtml('pasteinto.svg', $this->translator->trans($dc->table.'.pasteinto.1', [$row['id']], 'contao_'.$dc->table))
        );
    }

    private function renderArticlePasteAfterButton(DataContainer $dc, array $row, bool $cr, ?array $clipboard = null): string
    {
        $pageAdapter = $this->framework->getAdapter(PageModel::class);
        $pageModel = $pageAdapter->findByPk($row['pid']);

        // Do not show paste button for pages without content composition or articles in layout
        if (
            null === $pageModel
            || !$this->pageRegistry->supportsContentComposition($pageModel)
            || !$this->hasArticlesInLayout($pageModel)
        ) {
            return '';
        }

        if (
            $cr
            || ('cut' === $clipboard['mode'] && $clipboard['id'] === $row['id'])
            || ('cutAll' === $clipboard['mode'] && \in_array($row['id'], $clipboard['id'], true))
            || !$this->security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_ARTICLE_HIERARCHY, $pageModel)
        ) {
            return $this->image->getHtml('pasteafter_.svg').' ';
        }

        return sprintf(
            '<a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
            $this->backend->addToUrl('act='.$clipboard['mode'].'&amp;mode=1&amp;pid='.$row['id'].(!\is_array($clipboard['id']) ? '&amp;id='.$clipboard['id'] : '')),
            StringUtil::specialchars($this->translator->trans($dc->table.'.pasteafter.1', [$row['id']], 'contao_'.$dc->table)),
            $this->image->getHtml('pasteafter.svg', $this->translator->trans($dc->table.'.pasteafter.1', [$row['id']], 'contao_'.$dc->table))
        );
    }

    private function hasArticlesInLayout(PageModel $pageModel): bool
    {
        return null !== $this->getArticleColumnInLayout($pageModel);
    }

    private function getArticleColumnInLayout(PageModel $pageModel): ?string
    {
        $pageModel->loadDetails();

        /** @var LayoutModel|null $layout */
        $layout = $pageModel->getRelated('layout');

        if (null === $layout) {
            return 'main';
        }

        $columns = [];

        foreach (StringUtil::deserialize($layout->modules, true) as $config) {
            if (0 === (int) $config['mod']) {
                $columns[] = $config['col'];
            }
        }

        $columns = array_filter(array_unique($columns));

        if (empty($columns)) {
            return null;
        }

        if (\in_array('main', $columns, true)) {
            return 'main';
        }

        return reset($columns);
    }
}
