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
 * Picture configuration.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class PickerConfig implements \JsonSerializable
{
    /**
     * @var string
     */
    private $context;

    /**
     * @var array
     */
    private $extras = [];

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $current;

    /**
     * Constructor.
     *
     * @param string $context
     * @param array  $extras
     * @param string $value
     * @param string $current
     */
    public function __construct($context, array $extras = [], $value = '', $current = '')
    {
        $this->context = $context;
        $this->extras = $extras;
        $this->value = $value;
        $this->current = $current;
    }

    /**
     * Gets context.
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Gets value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Gets alias of the current picker.
     *
     * @return string
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Gets extras.
     *
     * @return array
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * Gets extra by name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getExtra($name)
    {
        return isset($this->extras[$name]) ? $this->extras[$name] : null;
    }

    /**
     * Sets extra by name.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setExtra($name, $value)
    {
        $this->extras[$name] = $value;
    }

    /**
     * Duplicates the configuration and overrides current picker alias.
     *
     * @param string $current
     *
     * @return PickerConfig
     */
    public function cloneForCurrent($current)
    {
        return new PickerConfig(
            $this->context,
            $this->extras,
            $this->value,
            $current
        );
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'context' => $this->context,
            'current' => $this->current,
            'value' => $this->value,
            'extras' => $this->extras,
        ];
    }

    /**
     * Initializes object from previous serialized data.
     *
     * @param array $data
     *
     * @return PickerConfig
     */
    public static function jsonUnserialize(array $data)
    {
        return new PickerConfig(
            $data['context'],
            $data['extras'],
            $data['value'],
            $data['current']
        );
    }
}
