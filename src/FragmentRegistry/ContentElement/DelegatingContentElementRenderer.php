<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\FragmentRegistry\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\ContaoCoreBundle;

/**
 * Class DelegatingContentElementRenderer.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class DelegatingContentElementRenderer implements ContentElementRendererInterface
{
    /**
     * @var ContentElementRendererInterface[]
     */
    private $renderers = [];

    /**
     * ChainFrontendModuleRenderer constructor.
     *
     * @param ContentElementRendererInterface[] $renderers
     */
    public function __construct(array $renderers)
    {
        foreach ($renderers as $renderer) {
            $this->addRenderer($renderer);
        }
    }

    /**
     * Adds a renderer.
     *
     * @param ContentElementRendererInterface $renderer
     */
    public function addRenderer(ContentElementRendererInterface $renderer)
    {
        $this->renderers[] = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ContentModel $contentModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): bool
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($contentModel, $inColumn, $scope)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ContentModel $contentModel, string $inColumn = 'main', string $scope = ContaoCoreBundle::SCOPE_FRONTEND): ?string
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($contentModel, $inColumn, $scope)) {
                return $renderer->render($contentModel, $inColumn, $scope);
            }
        }

        return null;
    }
}
