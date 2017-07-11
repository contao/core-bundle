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
 * Interface for picker factory.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
interface PickerFactoryInterface
{
    /**
     * Creates a picker or null if the context is not supported.
     *
     * @param PickerConfig  $config
     *
     * @return PickerInterface|null
     */
    public function create(PickerConfig $config);

    /**
     * Creates a picker from encoded URL data.
     *
     * @param string $payload
     *
     * @return PickerInterface|null
     */
    public function createFromPayload($payload);

    /**
     * Gets picker URL for given context and configuration.
     *
     * @param string $context
     * @param array  $extras
     * @param string $value
     *
     * @return string
     */
    public function getInitialUrl($context, array $extras = [], $value = '');
}
