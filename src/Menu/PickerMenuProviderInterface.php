<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

/**
 * Provides data for the picker menu.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
interface PickerMenuProviderInterface
{
    /**
     * Adds items to the menu.
     *
     * @param ItemInterface    $menu
     * @param FactoryInterface $factory
     */
    public function createMenu(ItemInterface $menu, FactoryInterface $factory);

    /**
     * Checks if a table is supported.
     *
     * @param string $table
     *
     * @return bool
     */
    public function isSupportedTable($table);

    /**
     * Processes the selected value.
     *
     * @param string $table
     * @param string $value
     *
     * @return string|null
     */
    public function processSelection($table, $value);

    /**
     * Returns the picker URL.
     *
     * @param array $params
     *
     * @return string|null
     */
    public function getPickerUrl(array $params = []);
}
