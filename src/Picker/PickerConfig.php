<?php

namespace Contao\CoreBundle\Picker;

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

    public function getContext()
    {
        return $this->context;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getCurrent()
    {
        return $this->current;
    }

    public function getExtras()
    {
        return $this->extras;
    }

    public function getExtra($name)
    {
        return isset($this->extras[$name]) ? $this->extras[$name] : null;
    }

    public function setExtra($name, $value)
    {
        $this->extras[$name] = $value;
    }

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
