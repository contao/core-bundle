<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Fragment;

interface FragmentOptionsAwareInterface
{
    /**
     * Sets the fragment options.
     *
     * @param array $options
     */
    public function setFragmentOptions(array $options): void;
}
