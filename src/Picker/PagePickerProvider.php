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

/**
 * Provides the page picker.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class PagePickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function getName()
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

    /**
     * {@inheritdoc}
     */
    public function supportsValue(PickerConfig $config)
    {
        if ('link' === $config->getContext() && false !== strpos($config->getValue(), '{{link_url::')) {
            return true;
        }

        if ('page' === $config->getContext() && is_numeric($config->getValue())) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters(PickerConfig $config)
    {
        return [
            'do' => 'page',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaTable()
    {
        return 'tl_page';
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaAttributes(PickerConfig $config)
    {
        $attributes = [];
        $value = $config->getValue();

        if ('page' === $config->getContext()) {
            $attributes = ['fieldType' => $config->getExtra('fieldType')];

            if (is_array($rootNodes = $config->getExtra('rootNodes'))) {
                $attributes['rootNodes'] = $rootNodes;
            }

            if ($value) {
                $attributes['value'] = array_map('intval', explode(',', $value));
            }
        } elseif ('link' === $config->getContext()) {
            $attributes = ['fieldType' => 'radio'];

            if ($value && false !== strpos($value, '{{link_url::')) {
                $attributes['value'] = str_replace(['{{link_url::', '}}'], '', $value);
            }
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function convertDcaValue(PickerConfig $config, $value)
    {
        if ('link' === $config->getContext()) {
            return '{{link_url::'.$value.'}}';
        }

        return (int) $value;
    }
}
