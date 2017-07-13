<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Picker;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provides the file picker.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class FilePickerProvider extends AbstractPickerProvider implements DcaPickerProviderInterface, FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var string
     */
    private $uploadPath;

    /**
     * Constructor.
     *
     * @param FactoryInterface         $menuFactory
     * @param TokenStorageInterface    $tokenStorage
     * @param ContaoFrameworkInterface $framework
     * @param string                   $uploadPath
     */
    public function __construct(FactoryInterface $menuFactory, TokenStorageInterface $tokenStorage, $uploadPath)
    {
        parent::__construct($menuFactory, $tokenStorage);

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
    protected function getLinkClass()
    {
        return 'filemounts';
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

        /** @var Validator $validator */
        $validator = $this->framework->getAdapter(Validator::class);

        if ('file' === $config->getContext() && $validator->isUuid($config->getValue())) {
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
            'do' => 'files',
        ];
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
        } elseif ('link' === $config->getContext() && $value) {
            $attributes['fieldType'] = 'radio';
            $attributes['filesOnly'] = true;

            if ($value) {
                if (false !== strpos($value, '{{file::')) {
                    $value = str_replace(['{{file::', '}}'], '', $value);
                }

                if (0 === strpos($value, $this->uploadPath . '/')) {
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
                /** @var StringUtil $stringUtil */
                $stringUtil = $this->framework->getAdapter(StringUtil::class);

                return '{{file::' . $stringUtil->binToUuid($file->uuid) . '}}';
            }
        }

        return $value;
    }

    /**
     * Converts UUID value to file path if possible.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function convertValueToPath($value)
    {
        /** @var Validator $validator */
        $validator = $this->framework->getAdapter(Validator::class);

        /** @var FilesModel $filesModel */
        $filesModel = $this->framework->getAdapter(FilesModel::class);

        if ($validator->isUuid($value) && ($file = $filesModel->findByUuid($value)) instanceof FilesModel) {
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
