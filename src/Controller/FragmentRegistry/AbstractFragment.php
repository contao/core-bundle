<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Abstract base class for fragments.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
abstract class AbstractFragment implements FragmentInterface
{
    /**
     * Returns the controller reference for that fragment.
     *
     * @param array $configuration
     *
     * @return ControllerReference
     */
     public function getControllerReference(array $configuration)
     {
         return new ControllerReference($this->getControllerServiceName(),
             $this->getControllerAttributes($configuration),
             $this->getControllerQueryParameters($configuration)
         );
     }

    /**
     * Gets the controller service name.
     *
     * @return string
     */
    protected function getControllerServiceName()
    {
        return get_called_class();
    }

    /**
     * Gets the controller attributes.
     *
     * @param array $configuration
     *
     * @return array
     */
     protected function getControllerAttributes(array $configuration)
     {
         return [
             'pageModel' => $GLOBALS['objPage']->id,
         ];
     }

    /**
     * Gets the controller query parameters.
     *
     * @param array $configuration
     *
     * @return array
     */
     protected function getControllerQueryParameters(array $configuration)
     {
         return [];
     }
 }
