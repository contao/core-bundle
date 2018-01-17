<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\FilesModel;
use Contao\Image;
use Contao\StringUtil;
use Contao\StyleSheets;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Theme
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var SessionInterface */
    protected $session;

    /** @var BackendUser */
    protected $user;

    /** @var ImageFactoryInterface */
    private $imageFactory;

    /**
     * Theme constructor.
     *
     * @param RequestStack          $requestStack
     * @param SessionInterface      $session
     * @param TokenStorageInterface $tokenStorage
     * @param ImageFactoryInterface $imageFactory
     */
    public function __construct(
        RequestStack $requestStack,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        ImageFactoryInterface $imageFactory
    ) {
        $this->requestStack = $requestStack;
        $this->session      = $session;
        $this->user         = $tokenStorage->getToken()->getUser();
        $this->imageFactory = $imageFactory;
    }

    /**
     * Check permissions to edit the table
     *
     * @throws AccessDeniedException
     */
    public function onCheckPermission(): void
    {
        if ($this->user->isAdmin) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        // Check the theme import and export permissions (see #5835)
        switch ($request->get('key')) {
            case 'importTheme':
                if (!$this->user->hasAccess('theme_import', 'themes')) {
                    throw new AccessDeniedException('Not enough permissions to import themes.');
                }
                break;

            case 'exportTheme':
                if (!$this->user->hasAccess('theme_import', 'themes')) {
                    throw new AccessDeniedException('Not enough permissions to export themes.');
                }
                break;
        }
    }


    /**
     * Add an image to each record
     *
     * @param array  $row
     * @param string $label
     *
     * @return string
     */
    public function onAddPreviewImage(array $row, string $label): string
    {
        if (!$row['screenshot']) {
            return $label;
        }

        $objFile = FilesModel::findByUuid($row['screenshot']);

        if (null === $objFile) {
            return $label;
        }

        $imageUrl = $this->imageFactory
            ->create(TL_ROOT . '/' . $objFile->path, [75, 50, 'center_top'])
            ->getUrl(TL_ROOT);

        return Image::getHtml($imageUrl, '', 'class="theme_preview"') . ' ' . $label;
    }


    /**
     * Check for modified style sheets and update them if necessary
     */
    public function onUpdateStyleSheet(): void
    {
        if ($this->session->get('style_sheet_update_all')) {
            // todo: make updateStyleSheets() static
            $styleSheets = new StyleSheets();
            $styleSheets->updateStyleSheets();
        }

        $this->session->set('style_sheet_update_all', null);
    }


    /**
     * Schedule a style sheet update
     *
     * This method is triggered when a single theme or multiple themes are
     * modified (edit/editAll) or duplicated (copy/copyAll).
     */
    public function onScheduleUpdate(): void
    {
        $this->session->set('style_sheet_update_all', true);
    }


    /**
     * Return all template folders as array
     *
     * @return array
     */
    public function onGetTemplateFolders(): array
    {
        return $this->doGetTemplateFolders('templates');
    }


    /**
     * Return all template folders as array
     *
     * @param string  $path
     * @param integer $level
     *
     * @return array
     */
    // todo: refactor?
    protected function doGetTemplateFolders(string $path, int $level = 0): array
    {
        $return = [];

        foreach (scan(TL_ROOT . '/' . $path) as $file) {
            if (is_dir(TL_ROOT . '/' . $path . '/' . $file)) {
                $return[$path . '/' . $file] = str_repeat(' &nbsp; &nbsp; ', $level) . $file;
                $return                      =
                    array_merge($return, $this->doGetTemplateFolders($path . '/' . $file, $level + 1));
            }
        }

        return $return;
    }


    /**
     * Return the "import theme" link
     *
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $class
     * @param string $attributes
     *
     * @return string
     */
    public function onImportTheme(string $href, string $label, string $title, string $class, string $attributes): string
    {
        if (!$this->user->hasAccess('theme_import', 'themes')) {
            return '';
        }

        return sprintf(
            '<a href="%s" class="%s" title="%s"%s>%s</a> ',
            Backend::addToUrl($href),
            $class,
            StringUtil::specialchars($title),
            $attributes,
            $label
        );
    }


    /**
     * Return the theme store link
     *
     * @return string
     */
    public function onThemeStore(): string
    {
        return sprintf(
            '<a href="https://themes.contao.org" title="%s" class="header_store" target="_blank" rel="noopener">%s</a>',
            StringUtil::specialchars($GLOBALS['TL_LANG']['tl_theme']['store'][1]),
            $GLOBALS['TL_LANG']['tl_theme']['store'][0]
        );
    }


    /**
     * Return the "edit CSS" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function onEditCss(
        array $row,
        string $href,
        string $label,
        string $title,
        string $icon,
        string $attributes
    ): string {
        return $this->renderButton('css', $row, $href, $label, $title, $icon, $attributes);
    }


    /**
     * Return the "edit modules" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function onEditModules(
        array $row,
        string $href,
        string $label,
        string $title,
        string $icon,
        string $attributes
    ): string {
        return $this->renderButton('modules', $row, $href, $label, $title, $icon, $attributes);
    }


    /**
     * Return the "edit page layouts" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function onEditLayout(
        array $row,
        string $href,
        string $label,
        string $title,
        string $icon,
        string $attributes
    ): string {
        return $this->renderButton('layout', $row, $href, $label, $title, $icon, $attributes);
    }


    /**
     * Return the "edit image sizes" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function onEditImageSizes(
        array $row,
        string $href,
        string $label,
        string $title,
        string $icon,
        string $attributes
    ): string {
        return $this->renderButton('image_sizes', $row, $href, $label, $title, $icon, $attributes);
    }


    /**
     * Return the "export theme" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function onExportTheme(
        array $row,
        string $href,
        string $label,
        string $title,
        string $icon,
        string $attributes
    ): string {
        return $this->renderButton('theme_export', $row, $href, $label, $title, $icon, $attributes);
    }


    /**
     * Render a button if the user has access to it.
     *
     * @param        $field
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    private function renderButton(
        $field,
        array $row,
        string $href,
        string $label,
        string $title,
        string $icon,
        string $attributes
    ) {
        if (!$this->user->hasAccess($field, 'themes')) {
            return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        }

        return sprintf(
            '<a href="%s" title="%s"%s>%s</a> ',
            Backend::addToUrl($href . '&amp;id=' . $row['id']),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label)
        );
    }
}
