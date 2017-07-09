<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Menu;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\FilesModel;
use Contao\StringUtil;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provides the file picker.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class FilePickerProvider extends AbstractMenuProvider implements PickerMenuProviderInterface, FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var string
     */
    private $uploadPath;

    /**
     * Constructor.
     *
     * @param RouterInterface       $router
     * @param RequestStack          $requestStack
     * @param TokenStorageInterface $tokenStorage
     * @param string                $uploadPath
     * @param UriSigner|null        $uriSigner
     */
    public function __construct(RouterInterface $router, RequestStack $requestStack, TokenStorageInterface $tokenStorage, $uploadPath, UriSigner $uriSigner = null)
    {
        parent::__construct($router, $requestStack, $tokenStorage, $uriSigner);

        $this->uploadPath = $uploadPath;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($context)
    {
        return 'file' === $context || 'link' === $context;
    }

    /**
     * {@inheritdoc}
     */
    public function createMenu(ItemInterface $menu, FactoryInterface $factory)
    {
        $user = $this->getUser();

        if ($user->hasAccess('files', 'modules')) {
            $this->addMenuItem($menu, $factory, 'files', 'filePicker', 'filemounts');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTable($table)
    {
        return 'tl_files' === $table;
    }

    /**
     * {@inheritdoc}
     */
    public function processSelection($value)
    {
        $value = rawurldecode($value);

        /** @var FilesModel $adapter */
        $adapter = $this->framework->getAdapter(FilesModel::class);

        if (($model = $adapter->findByPath($value)) instanceof FilesModel) {
            return json_encode([
                'content' => $value,
                'tag' => sprintf('{{file::%s}}', StringUtil::binToUuid($model->uuid)),
            ]);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function canHandle(Request $request)
    {
        if ($request->query->get('context') === 'file') {
            return true;
        }

        if (!$request->query->has('value')) {
            return false;
        }

        $value = $request->query->get('value');

        return 0 === strpos($value, $this->uploadPath.'/') || false !== strpos($value, '{{file::');
    }

    /**
     * {@inheritdoc}
     */
    public function getPickerUrl(Request $request)
    {
        $params = $request->query->all();
        $params['do'] = 'files';

        if (isset($params['value']) && 0 === strpos($params['value'], '{{')) {
            $value = str_replace(['{{file::', '}}'], '', $params['value']);

            /** @var FilesModel $adapter */
            $adapter = $this->framework->getAdapter(FilesModel::class);

            if (($model = $adapter->findByUuid($value)) instanceof FilesModel) {
                $params['value'] = $model->path;
            }
        }

        return $this->route('contao_backend', $params);
    }

    /**
     * {@inheritdoc}
     */
    protected function getParametersFromRequest(Request $request)
    {
        $params = parent::getParametersFromRequest($request);

        if (null === $this->uriSigner || !$this->uriSigner->check($request->getUri())) {
            return $params;
        }

        foreach (['files', 'filesOnly', 'path', 'extensions'] as $key) {
            if ($request->query->has($key)) {
                $params[$key] = $request->query->get($key);
            }
        }

        return $params;
    }
}
