<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Picker;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;
use Knp\Menu\FactoryInterface;

/**
 * Provides the file picker.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class FilePickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface, FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var string
     */
    private $uploadPath;

    /**
     * Constructor.
     *
     * @param FactoryInterface $menuFactory
     * @param string           $uploadPath
     */
    public function __construct(FactoryInterface $menuFactory, $uploadPath)
    {
        parent::__construct($menuFactory);

        $this->uploadPath = $uploadPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filePicker';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsContext($context)
    {
        return in_array($context, ['file', 'link'], true) && $this->getUser()->hasAccess('files', 'modules');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(PickerConfig $config)
    {
        if ('link' === $config->getContext()
            && (false !== strpos($config->getValue(), '{{file::')
                || 0 === strpos($config->getValue(), $this->uploadPath)
            )
        ) {
            return true;
        }

        if ('file' === $config->getContext() && Validator::isUuid($config->getValue())) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaTable()
    {
        return 'tl_files';
    }

    /**
     * {@inheritdoc}
     */
    public function getDcaAttributes(PickerConfig $config)
    {
        $attributes = [];
        $value = $config->getValue();

        if ('file' === $config->getContext()) {
            $attributes += array_intersect_key(
                $config->getExtras(),
                array_flip(['fieldType', 'files', 'filesOnly', 'path', 'extensions'])
            );

            if ($value) {
                $attributes['value'] = [];

                foreach (explode(',', $value) as $v) {
                    $attributes['value'][] = $this->urlEncode($this->convertValueToPath($v));
                }
            }
        } elseif ('link' === $config->getContext()) {
            $attributes['fieldType'] = 'radio';
            $attributes['filesOnly'] = true;

            if ($value) {
                if (false !== strpos($value, '{{file::')) {
                    $value = str_replace(['{{file::', '}}'], '', $value);
                }

                if (0 === strpos($value, $this->uploadPath.'/')) {
                    $attributes['value'] = $this->urlEncode($value);
                } else {
                    $attributes['value'] = $this->urlEncode($this->convertValueToPath($value));
                }
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
            /** @var FilesModel $filesModel */
            $filesModel = $this->framework->getAdapter(FilesModel::class);
            $file = $filesModel->findByPath(rawurldecode($value));

            if (null !== $file) {
                return '{{file::'.StringUtil::binToUuid($file->uuid).'}}';
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLinkClass()
    {
        return 'filemounts';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRouteParameters(PickerConfig $config)
    {
        return ['do' => 'files'];
    }

    /**
     * Converts the UUID value to a file path if possible.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function convertValueToPath($value)
    {
        /** @var FilesModel $filesModel */
        $filesModel = $this->framework->getAdapter(FilesModel::class);

        if (Validator::isUuid($value) && ($file = $filesModel->findByUuid($value)) instanceof FilesModel) {
            return $file->path;
        }

        return $value;
    }

    /**
     * Urlencodes a file path preserving slashes.
     *
     * @param string $strPath
     *
     * @return string
     *
     * @see \Contao\System::urlEncode()
     */
    private function urlEncode($strPath)
    {
        return str_replace('%2F', '/', rawurlencode($strPath));
    }
}
