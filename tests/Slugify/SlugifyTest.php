<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Slugify;

use Cocur\Slugify\Slugify as CocurSlugify;
use Contao\CoreBundle\Slugify\Slugify;

/**
 * Tests the Slugify class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class SlugifyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $slugify = new Slugify(new CocurSlugify());

        $this->assertInstanceOf('Contao\CoreBundle\Slugify\Slugify', $slugify);
    }

    /**
     * Tests the slugify() method.
     */
    public function testSlugify()
    {
        $slugify = new Slugify(new CocurSlugify());

        $this->assertEquals('fuer', $slugify->slugify('für', 'de'));
        $this->assertEquals('fur', $slugify->slugify('für', 'tr'));
        $this->assertEquals('aepfel-und-birnen', $slugify->slugify('Äpfel und Birnen', 'de'));
        $this->assertEquals('groesse', $slugify->slugify('Größe', 'de'));
        $this->assertEquals('groesze', $slugify->slugify('Größe', 'de_AT'));
    }

    /**
     * Tests the getRulesetForLanguage() method.
     */
    public function getRulesetForLanguage()
    {
        $slugify = new Slugify(new CocurSlugify());

        $this->assertEquals('german', $slugify->getRulesetForLanguage('de'));
        $this->assertEquals('german', $slugify->getRulesetForLanguage('de_DE'));
        $this->assertEquals('german', $slugify->getRulesetForLanguage('de_CH'));
        $this->assertEquals('austrian', $slugify->getRulesetForLanguage('de_AT'));
        $this->assertEquals('polish', $slugify->getRulesetForLanguage('pl'));
    }
}
