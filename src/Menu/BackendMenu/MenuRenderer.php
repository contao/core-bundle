<?php

namespace Contao\CoreBundle\Menu\BackendMenu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Renderer\RendererInterface;
use Twig\Environment;

class MenuRenderer implements RendererInterface
{
    private $templating;

    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    public function render(ItemInterface $item, array $options = [])
    {
        $lang = [
            'skipNavigation' => \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['skipNavigation'])
        ];

        $templateData = array_merge([
            'tree' => $item,
            'lang' => $lang
        ], $options);

        return $this->templating->render('ContaoCoreBundle:Backend:be_menu.html.twig', $templateData);
    }
}