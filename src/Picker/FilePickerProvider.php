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
use Contao\DataContainer;
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
class FilePickerProvider extends AbstractPickerProvider implements FrameworkAwareInterface
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
    public function getAlias()
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
        return ('link' === $config->getContext() && false !== strpos($config->getValue(), '{{file::'));
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
    public function prepareConfig(PickerConfig $config, DataContainer $dc)
    {
        if ('tl_files' !== $dc->table) {
            return null;
        }

        $result = ['fieldType' => $config->getExtra('fieldType')];
        $value = $config->getValue();

        if ('file' === $config->getContext()) {
            $result += array_intersect_key(
                $config->getExtras(),
                array_flip(['files', 'filesOnly', 'path', 'extensions'])
            );

            if ($value) {
                $result['value'] = $this->convertValueToPath($value);
            }
        } elseif ('link' === $config->getContext() && $value) {
            if (false !== strpos($value, '{{file::')) {
                $value = str_replace(['{{file::', '}}'], '', $value);
            }

            if (0 === strpos($value, $this->uploadPath.'/')) {
                $result['value'] = $value;
            } else {
                $result['value'] = $this->convertValueToPath($value);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareValue(PickerConfig $config, $value)
    {
        if ('link' === $config->getContext()) {
            /** @var FilesModel $filesModel */
            $filesModel = $this->framework->getAdapter(FilesModel::class);
            $file = $filesModel->findByPath($value);

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

        return '';
    }
}
