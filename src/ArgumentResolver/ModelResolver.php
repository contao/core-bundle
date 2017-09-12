<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
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

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $id = $request->attributes->getInt($argument->getName());

        yield call_user_func($argument->getType() . '::findByPk', $id);
    }
}
