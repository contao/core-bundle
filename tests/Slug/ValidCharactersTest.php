<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Slug;

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\SlugValidCharactersEvent;
use Contao\CoreBundle\Slug\ValidCharacters;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Tests the ValidCharacters class.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class ValidCharactersTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->assertInstanceOf('Contao\CoreBundle\Slug\ValidCharacters', new ValidCharacters($eventDispatcher));
    }

    /**
     * Tests the getOptions() method.
     */
    public function testGetOptions()
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                ContaoCoreEvents::SLUG_VALID_CHARACTERS,
                $this->callback(
                    function (SlugValidCharactersEvent $event) use (&$path) {
                        $this->assertInternalType('array', $event->getOptions());
                        $this->assertArrayHasKey('\pN\p{Ll}', $event->getOptions());
                        $this->assertArrayHasKey('\pN\pL', $event->getOptions());
                        $this->assertArrayHasKey('0-9a-z', $event->getOptions());
                        $this->assertArrayHasKey('0-9a-zA-Z', $event->getOptions());

                        return true;
                    }
                )
            )
        ;

        $validCharacters = new ValidCharacters($eventDispatcher);
        $GLOBALS['TL_LANG']['MSC']['validCharacters'] = [
            'unicodeLowercase' => 'unicodeLowercase',
            'unicode' => 'unicode',
            'asciiLowercase' => 'asciiLowercase',
            'ascii' => 'ascii',
        ];

        $options = $validCharacters->getOptions();

        $this->assertInternalType('array', $options);
        $this->assertArrayHasKey('\pN\p{Ll}', $options);
        $this->assertArrayHasKey('\pN\pL', $options);
        $this->assertArrayHasKey('0-9a-z', $options);
        $this->assertArrayHasKey('0-9a-zA-Z', $options);

        unset($GLOBALS['TL_LANG']['MSC']['validCharacters']);
    }
}
