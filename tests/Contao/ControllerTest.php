<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Contao;

use Contao\Controller;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Controller class.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 *
 * @group contao3
 */
class ControllerTest extends TestCase
{
    public function testGetTimeZones()
    {
        $arrTimeZones = Controller::getTimeZones();

        $this->assertCount(9, $arrTimeZones['General']);
        $this->assertCount(51, $arrTimeZones['Africa']);
        $this->assertCount(140, $arrTimeZones['America']);
        $this->assertCount(10, $arrTimeZones['Antarctica']);
        $this->assertCount(83, $arrTimeZones['Asia']);
        $this->assertCount(11, $arrTimeZones['Atlantic']);
        $this->assertCount(22, $arrTimeZones['Australia']);
        $this->assertCount(4, $arrTimeZones['Brazil']);
        $this->assertCount(9, $arrTimeZones['Canada']);
        $this->assertCount(2, $arrTimeZones['Chile']);
        $this->assertCount(53, $arrTimeZones['Europe']);
        $this->assertCount(11, $arrTimeZones['Indian']);
        $this->assertCount(4, $arrTimeZones['Brazil']);
        $this->assertCount(3, $arrTimeZones['Mexico']);
        $this->assertCount(40, $arrTimeZones['Pacific']);
        $this->assertCount(13, $arrTimeZones['United States']);
    }

    public function testGenerateMargin()
    {
        $arrMargins = [
            'top' => '40px',
            'right' => '10%',
            'bottom' => '-2px',
            'left' => '-50%',
            'unit' => '',
        ];

        $this->assertEquals(
            'margin:40px 10% -2px -50%;',
            Controller::generateMargin($arrMargins)
        );
    }
}
