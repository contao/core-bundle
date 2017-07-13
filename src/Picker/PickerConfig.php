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
     * Encodes the picker configuration for use in the URL.
     *
     * @param bool $gzip
     *
     * @return string
     */
    public function urlEncode($gzip = true)
    {
        $data = json_encode($this);

        if ($gzip
            && function_exists('gzencode')
            && function_exists('gzdecode')
            && false !== ($encoded = @gzencode($data))
        ) {
            $data = $encoded;
        }

        return base64_encode($data);
    }

    /**
     * Initializes object from URL data.
     *
     * @param string $data
     *
     * @return PickerConfig
     *
     * @throws \InvalidArgumentException
     */
    public static function urlDecode($data)
    {
        $data = base64_decode($data, true);

        if (function_exists('gzdecode') && false !== ($uncompressed = @gzdecode($data))) {
            $data = $uncompressed;
        }

        $data = @json_decode($data, true);

        if (null === $data) {
            throw new \InvalidArgumentException('Invalid JSON data');
        }

        return new PickerConfig(
            $data['context'],
            $data['extras'],
            $data['value'],
            $data['current']
        );
    }
}
