<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

/**
 * Interface for fragments.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
interface FragmentInterface
{
    /**
     * @return string
     */
    public function getName();
}
