<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Events;

/**
 * Defines Constants for all Contao events.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
final class ContaoEvents
{
    /**
     * The contao.initialize_system event is triggered when the Contao
     * framework is initialized.
     *
     * The event listener method receives a
     * Symfony\Component\EventDispatcher\Event instance.
     *
     * @var string
     */
    const INITIALIZE_SYSTEM = 'contao.initialize_system';
}
