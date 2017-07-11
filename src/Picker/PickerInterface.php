<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Picker;

use Knp\Menu\ItemInterface;

/**
 * Picker interface.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
interface PickerInterface
{
    /**
     * Gets the picker config.
     *
     * @return PickerConfig
     */
    public function getConfig();

    /**
     * Gets the picker menu.
     *
     * @return ItemInterface
     */
    public function getMenu();

    /**
     * Gets the current provider.
     *
     * @return PickerProviderInterface|null
     */
    public function getCurrentProvider();

    /**
     * Gets URL to the current picker tab.
     *
     * @return string
     */
    public function getCurrentUrl();
}
