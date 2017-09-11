<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FragmentRegistry\FragmentInterface;

/**
 * Interface PageTypeInterface
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FrontendModuleInterface extends FragmentInterface
{
    /**
     * Gets the category of the front end module fragment
     * as a string.
     *
     * @return string
     */
    public static function getCategory();
}
