<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Menu;

use Contao\CoreBundle\Menu\FilePickerProvider;
use Contao\CoreBundle\Menu\PickerMenuProviderInterface;
use Contao\CoreBundle\Tests\TestCase;
use Knp\Menu\MenuFactory;

/**
 * Tests the FilePickerProvider class.
 *
 * @author Leo Feyer <https:/github.com/leofeyer>
 */
class FilePickerProviderTest extends TestCase
{
    /**
     * @var PickerMenuProviderInterface
     */
    private $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->provider = $this->mockPickerProvider(FilePickerProvider::class);
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Menu\FilePickerProvider', $this->provider);
    }

    /**
     * Tests the createMenu() method.
     */
    public function testCreateMenu()
    {
        $factory = new MenuFactory();
        $menu = $factory->createItem('foo');

        $this->provider->createMenu($menu, $factory);

        $this->assertTrue($menu->hasChildren());

        $filePicker = $menu->getChild('filePicker');

        $this->assertNotNull($filePicker);
        $this->assertEquals('filemounts', $filePicker->getLinkAttribute('class'));
        $this->assertEquals('contao_backend:do=files', $filePicker->getUri());
    }

    /**
     * Tests the supports() method.
     */
    public function testSupports()
    {
        $this->assertTrue($this->provider->supports('tl_files'));
        $this->assertFalse($this->provider->supports('tl_page'));
    }

    /**
     * Tests the processSelection() method.
     */
    public function testProcessSelection()
    {
        $this->assertEquals('files/test/foo.jpg', $this->provider->processSelection('files/test/foo.jpg'));
    }

    /**
     * Tests the canHandle() method.
     */
    public function testCanHandle()
    {
        $this->assertTrue($this->provider->canHandle('files/test/foo.jpg'));
        $this->assertFalse($this->provider->canHandle('{{link_url::2}}'));
    }

    /**
     * Tests the getPickerUrl() method.
     */
    public function testGetPickerUrl()
    {
        $this->assertEquals(
            'contao_backend:value=files/test/foo.jpg:do=files',
            $this->provider->getPickerUrl(['value' => 'files/test/foo.jpg'])
        );
    }
}
