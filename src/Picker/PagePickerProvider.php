<?php

namespace Contao\CoreBundle\Picker;

class PagePickerProvider extends AbstractPickerProvider
{

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'pagePicker';
    }

    /**
     * {@inheritdoc}
     */
    protected function getLinkClass()
    {
        return 'pagemounts';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext($context)
    {
        return in_array($context, ['page', 'link'], true) && $this->getUser()->hasAccess('page', 'modules');
    }

    public function supportsValue(PickerConfig $config)
    {
        if ('page' === $config->getContext() && is_numeric($config->getValue())) {
            return true;
        }

        if ('link' === $config->getContext() && false !== strpos($config->getValue(), '{{link_url::')) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters()
    {
        return [
            'do' => 'page',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function prepareConfig(PickerConfig $config)
    {
        $result = ['fieldType' => $config->getExtra('fieldType')];
        $value = $config->getValue();

        if ('page' === $config->getContext()) {
            if ($value) {
                $result['value'] = array_map('intval', explode(',', $value));
            }

            if (is_array($rootNodes = $config->getExtra('rootNodes'))) {
                $result['rootNodes'] = $rootNodes;
            }
        } elseif ('link' === $config->getContext() && false !== strpos($value, '{{link_url::')) {
            $result['value'] = str_replace(['{{link_url::', '}}'], '', $value);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareValue(PickerConfig $config, $value)
    {
        if ('link' === $config->getContext()) {
            return '{{link_url::'.$value.'}}';
        }

        return (int) $value;
    }
}
