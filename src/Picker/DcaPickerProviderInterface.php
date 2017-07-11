<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Picker;

/**
 * Interface for DCA picker providers.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
interface DcaPickerProviderInterface extends PickerProviderInterface
{
    /**
     * Returns the DCA table for this provider.
     *
     * @return bool
     */
    public function getDcaTable();

    /**
     * Gets attributes for DataContainer.
     *
     * @param PickerConfig $config
     *
     * @return array
     */
    public function getDcaAttributes(PickerConfig $config);

    /**
     * Converts DCA value for picker selection.
     *
     * @param PickerConfig $config
     * @param mixed        $value
     *
     * @return mixed
     */
    public function convertDcaValue(PickerConfig $config, $value);
}
