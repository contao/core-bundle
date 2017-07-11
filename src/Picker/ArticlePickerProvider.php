<?php

namespace Contao\CoreBundle\Picker;

class ArticlePickerProvider extends AbstractPickerProvider
{

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'articlePicker';
    }

    /**
     * {@inheritdoc}
     */
    protected function getLinkClass()
    {
        return 'articles';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext($context)
    {
        return 'link' === $context && $this->getUser()->hasAccess('article', 'modules');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(PickerConfig $config)
    {
        return false !== strpos($config->getValue(), '{{article_url::');
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters()
    {
        return [
            'do' => 'article',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function prepareConfig(PickerConfig $config)
    {
        $result = ['fieldType' => $config->getExtra('fieldType')];

        if ('link' === $config->getContext() && $this->supportsValue($config)) {
            $result['value'] = str_replace(['{{article_url::', '}}'], '', $config->getValue());
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareValue(PickerConfig $config, $value)
    {
        return '{{article_url::'.$value.'}}';
    }
}
