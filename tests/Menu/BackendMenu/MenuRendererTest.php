<?php

namespace Contao\CoreBundle\Tests\Menu\BackendMenu;

use Contao\CoreBundle\Menu\BackendMenu\MenuBuilder;
use Contao\CoreBundle\Menu\BackendMenu\MenuRenderer;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class MenuRendererTest extends TestCase
{
    /** @var Environment|\PHPUnit_Framework_MockObject_MockObject */
    protected $templating;

    /** @var MenuRenderer */
    protected $renderer;

    protected function setUp()
    {
        $GLOBALS['TL_LANG']['MSC']['skipNavigation'] = 'Skip navigation';

        $this->templating = $this->createMock(Environment::class);
        $this->renderer = new MenuRenderer($this->templating);
    }

    protected function tearDown()
    {
        unset($GLOBALS['TL_LANG']['MSC']['skipNavigation']);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Menu\BackendMenu\MenuRenderer', $this->renderer);
    }

    public function testRendersBackendMenuTemplate()
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $node */
        $node = $this->createMock(ItemInterface::class);

        $this->templating
            ->expects($this->once())
            ->method('render')
            ->with('ContaoCoreBundle:Backend:be_menu.html.twig', [
                'tree' => $node,
                'lang' => [
                    'skipNavigation' => $GLOBALS['TL_LANG']['MSC']['skipNavigation']
                ]
            ]);

        $this->renderer->render($node);
    }
}