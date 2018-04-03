<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Contao;

use Contao\System;
use PHPUnit\Framework\TestCase;

/**
 * Tests the System class.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 *
 * @group contao3
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SystemTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        // Manually load the System class so it's not using the fixture
        require __DIR__.'/../../src/Resources/contao/library/Contao/System.php';
    }

    public function testGetFormattedNumber()
    {
        $strNumber = '12004.34564';

        // override settings
        $GLOBALS['TL_LANG']['MSC']['decimalSeparator'] = '.';
        $GLOBALS['TL_LANG']['MSC']['thousandsSeparator'] = '';

        $arrNumberWithDifferentDecimals = [
            0 => '12004',
            1 => '12004.3',
            2 => '12004.35',
            3 => '12004.346',
            4 => '12004.3456',
            5 => '12004.34564',
        ];

        foreach ($arrNumberWithDifferentDecimals as $intDecimals => $strFormattedNumber) {
            $this->assertEquals(System::getFormattedNumber($strNumber, $intDecimals), $strFormattedNumber);
        }

        // now test again with set thousandsSeparator
        $GLOBALS['TL_LANG']['MSC']['thousandsSeparator'] = ','; // override
        $arrNumberWithDifferentDecimalsWithSeperator = [
            0 => '12,004',
            1 => '12,004.3',
            2 => '12,004.35',
            3 => '12,004.346',
            4 => '12,004.3456',
            5 => '12,004.34564',
        ];

        foreach ($arrNumberWithDifferentDecimalsWithSeperator as $intDecimals => $strFormattedNumber) {
            $this->assertEquals(System::getFormattedNumber($strNumber, $intDecimals), $strFormattedNumber);
        }
    }

    public function testAnonymizeIp()
    {
        // Enable IP anonymization
        $GLOBALS['TL_CONFIG']['privacyAnonymizeIp'] = true;
        $this->assertEquals(System::anonymizeIp('172.16.254.112'), '172.16.254.0');
        $this->assertEquals(System::anonymizeIp('2001:0db8:85a3:0042:0000:8a2e:0370:7334'), '2001:0db8:85a3:0042:0000:8a2e:0370:0000');

        // Disable IP anonymization
        $GLOBALS['TL_CONFIG']['privacyAnonymizeIp'] = false;
        $this->assertEquals(System::anonymizeIp('172.16.254.112'), '172.16.254.112');
        $this->assertEquals(System::anonymizeIp('2001:0db8:85a3:0042:0000:8a2e:0370:7334'), '2001:0db8:85a3:0042:0000:8a2e:0370:7334');
    }
}
