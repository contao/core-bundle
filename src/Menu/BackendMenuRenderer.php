<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Renderer\RendererInterface;
use Twig\Environment;

class BackendMenuRenderer implements RendererInterface
{
    /**
     * @var Environment
     */
    private $templating;

    /**
     * @param Environment $templating
     */
    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ItemInterface $item, array $options = []): string
    {
        $lang = [
            'skipNavigation' => \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['skipNavigation']),
        ];

        return $this->templating->render(
            'ContaoCoreBundle:Backend:be_menu.html.twig',
            [
                'tree' => $item,
                'lang' => $lang,
            ]
        );
    }
}
