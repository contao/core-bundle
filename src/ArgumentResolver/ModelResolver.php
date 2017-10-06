<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\ArgumentResolver;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Argument resolver for Contao Models.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class ModelResolver implements ArgumentValueResolverInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * ModelResolver constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (!$request->attributes->has($argument->getName())) {
            return false;
        }

        $this->framework->initialize();

        if (!is_a($argument->getType(), Model::class, true)) {
            return false;
        }

        if (!$argument->isNullable() && null === $this->fetchModel($request, $argument)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $this->fetchModel($request, $argument);
    }

    /**
     * Fetches the model.
     *
     * @param Request          $request
     * @param ArgumentMetadata $argument
     *
     * @return Model|null
     */
    private function fetchModel(Request $request, ArgumentMetadata $argument)
    {
        $id = $request->attributes->getInt($argument->getName());

        /** @var Model $model */
        $model = $this->framework->getAdapter($argument->getType());

        return $model->findByPk($id);
    }
}
