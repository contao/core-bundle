<?php

namespace Contao\CoreBundle\Picker;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class Picker implements PickerInterface
{
    /**
     * @var FactoryInterface
     */
    private $menuFactory;

    /**
     * @var PickerProviderInterface[]
     */
    private $providers;

    /**
     * @var PickerConfig
     */
    private $config;

    /**
     * @var ItemInterface
     */
    private $menu;

    /**
     * Constructor.
     *
     * @param FactoryInterface          $menuFactory
     * @param PickerProviderInterface[] $providers
     * @param PickerConfig              $config
     */
    public function __construct(FactoryInterface $menuFactory, array $providers, PickerConfig $config)
    {
        $this->menuFactory = $menuFactory;
        $this->providers = $providers;
        $this->config = $config;
    }

    public function getMenu()
    {
        $this->createMenu();

        return $this->menu;
    }

    public function getUrlForValue(PickerConfig $config)
    {
        $this->createMenu();

        if (!$this->menu->count()) {
            throw new \RuntimeException('No picker menu items found.');
        }

        /** @var ItemInterface $menu */
        foreach ($this->menu as $menu) {
            $picker = $menu->getExtra('provider');
            if ($picker instanceof PickerProviderInterface && $picker->supportsValue($config)) {
                return $menu->getUri();
            }
        }

        return $this->menu->getFirstChild()->getUri();
    }

    public function getCurrentConfig()
    {
        foreach ($this->providers as $provider) {
            if ($provider->isCurrent($this->config)) {
                return $provider->prepareConfig($this->config);
            }
        }

        return null;
    }

    /**
     * Gets current value for the picker.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getCurrentValue($value)
    {
        foreach ($this->providers as $provider) {
            if ($provider->isCurrent($this->config)) {
                return $provider->prepareValue(
                    $this->config,
                    $value
                );
            }
        }

        return $value;
    }

    private function createMenu()
    {
        if (null !== $this->menu) {
            return;
        }

        $this->menu = $this->menuFactory->createItem('picker');

        foreach ($this->providers as $provider) {
            $item = $provider->createMenuItem($this->config);
            $item->setExtra('provider', $provider);
            $this->menu->addChild($item);
        }
    }
}
