<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Fragment;

interface FragmentOptionsAwareInterface
{
    /**
     * Sets the fragment options.
     */
    public function setFragmentOptions(array $options): void;
}
