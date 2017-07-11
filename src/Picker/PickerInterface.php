<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Picker;

use Contao\DataContainer;
use Knp\Menu\ItemInterface;

/**
 * Picker interface.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
interface PickerInterface
{
    /**
     * Gets picker menu.
     *
     * @return ItemInterface
     */
    public function getMenu();

    /**
     * Gets initial URL to the picker.
     *
     * @return string
     */
    public function getUrlForValue();

    /**
     * Gets attributes for current picker.
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getCurrentConfig(DataContainer $dc);

    /**
     * Converts value to picker result.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getCurrentValue($value);
}
