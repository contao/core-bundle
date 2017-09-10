<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Event;

use Contao\CoreBundle\Event\SlugValidCharactersEvent;
use PHPUnit\Framework\TestCase;

/**
 * Tests the SlugValidCharactersEvent class.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class SlugValidCharactersEventTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $event = new SlugValidCharactersEvent([]);

        $this->assertInstanceOf('Contao\CoreBundle\Event\SlugValidCharactersEvent', $event);
    }

    /**
     * Tests the options setter and getter.
     */
    public function testOptionsSetterGetter()
    {
        $event = new SlugValidCharactersEvent(['a-z' => 'ASCII']);

        $this->assertSame(['a-z' => 'ASCII'], $event->getOptions());

        $event->setOptions(['\pL' => 'Unicode', '0-9' => 'Digits']);

        $this->assertSame(['\pL' => 'Unicode', '0-9' => 'Digits'], $event->getOptions());
    }
}
