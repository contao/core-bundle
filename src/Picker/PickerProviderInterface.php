<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Picker;

use Knp\Menu\ItemInterface;

interface PickerProviderInterface
{
    /**
     * Returns the unique name for this picker.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the URL to the picker based on the current value.
     *
     * @return string
     */
    public function getUrl(PickerConfig $config);

    /**
     * Creates the menu item for this picker.
     *
     * @return ItemInterface
     */
    public function createMenuItem(PickerConfig $config);

    /**
     * Returns whether the picker supports the given context.
     *
     * @param string $context
     *
     * @return bool
     */
    public function supportsContext($context);

    /**
     * Returns whether the picker supports the given value.
     *
     * @return bool
     */
    public function supportsValue(PickerConfig $config);

    /**
     * Returns whether the picker is currently active.
     *
     * @return bool
     */
    public function isCurrent(PickerConfig $config);
}
