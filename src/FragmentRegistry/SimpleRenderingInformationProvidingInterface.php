<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\FragmentRegistry;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface SimpleRenderingInformationProvidingInterface.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface SimpleRenderingInformationProvidingInterface
{
    /**
     * @param Request $request
     * @param array   $attributes
     *
     * @return array
     */
    public function getControllerRequestAttributes(Request $request, array $attributes): array;

    /**
     * @param Request $request
     * @param array   $parameters
     *
     * @return array
     */
    public function getControllerQueryParameters(Request $request, array $parameters): array;
}
