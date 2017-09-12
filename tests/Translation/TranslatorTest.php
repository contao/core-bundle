<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Translation;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Translation\Translator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Tests the TokenGenerator class.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class TranslatorTest extends TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $originalTranslator = $this->createMock(TranslatorInterface::class);
        $framework = $this->createMock(ContaoFrameworkInterface::class);

        $translator = new Translator($originalTranslator, $framework);

        $this->assertInstanceOf('Contao\CoreBundle\Translation\Translator', $translator);
        $this->assertInstanceOf('Symfony\Component\Translation\TranslatorInterface', $translator);
    }
}
