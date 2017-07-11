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
 * Interface for picker builder.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
interface PickerBuilderInterface
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
     * Creates a picker from JSON data.
     *
     * @param string $json
     *
     * @return PickerInterface|null
     */
    public function createFromJson($json);

    /**
     * Returns whether the given context is supported.
     *
     * @param string $context
     *
     * @return bool
     */
    public function supportsContext($context);

    /**
     * Gets picker URL for given context and configuration.
     *
     * @param string $context
     * @param array  $extras
     * @param string $value
     *
     * @return string
     */
    public function getUrl($context, array $extras = [], $value = '');
}
