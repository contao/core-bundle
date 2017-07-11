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
 * Picker provider interface.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
interface PickerProviderInterface
{
    /**
     * Gets the unique name for this picker.
     *
     * @return string
     */
    public function getName();

    /**
     * Creates menu item for this picker.
     *
     * @param PickerConfig $config
     *
     * @return ItemInterface
     */
    public function createMenuItem(PickerConfig $config);

    /**
     * Returns whether the picker is supported for given context.
     *
     * @param string $context
     *
     * @return bool
     */
    public function supportsContext($context);

    /**
     * Returns whether the picker supports given value.
     *
     * @param PickerConfig $config
     *
     * @return bool
     */
    public function supportsValue(PickerConfig $config);

    /**
     * Returns whether the picker is currently active.
     *
     * @param PickerConfig $config
     *
     * @return bool
     */
    public function isCurrent(PickerConfig $config);
}
