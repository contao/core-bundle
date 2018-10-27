<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\HttpKernel;

use Contao\CoreBundle\Fragment\FragmentRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class ControllerResolver implements ControllerResolverInterface
{
    /**
     * @var ControllerResolverInterface
     */
    private $resolver;

    /**
     * @var FragmentRegistry
     */
    private $registry;

    public function __construct(ControllerResolverInterface $resolver, FragmentRegistry $registry)
    {
        $this->resolver = $resolver;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        if ($request->attributes->has('_controller')) {
            $fragmentConfig = $this->registry->get($request->attributes->get('_controller'));

            if (null !== $fragmentConfig) {
                $request->attributes->set('_controller', $fragmentConfig->getController());
            }
        }

        return $this->resolver->getController($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments(Request $request, $controller): array
    {
        if (!method_exists($this->resolver, 'getArguments')) {
            return [];
        }

        return $this->resolver->getArguments($request, $controller);
    }
}
