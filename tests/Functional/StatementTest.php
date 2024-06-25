<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Functional;

use Contao\Database;
use Contao\PageModel;
use Contao\System;
use Contao\TestCase\FunctionalTestCase;
use Doctrine\DBAL\Driver\Mysqli\Driver as MysqliDriver;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver as PdoMysqlDriver;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

class StatementTest extends FunctionalTestCase
{
    use ExpectDeprecationTrait;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        System::setContainer(static::bootKernel()->getContainer());
    }

    /**
     * @group legacy
     */
    public function testExecuteStatementBackwardsCompatibility(): void
    {
        $this->expectDeprecation('%sPassing more parameters than "?" tokens has been deprecated%s');

        $connection = System::getContainer()->get('database_connection');
        $driver = $connection->getDriver();
        $errorReporting = error_reporting();

        // Silence MySQLi errors in PHP 7.4
        if (\PHP_VERSION_ID < 80000 && $driver instanceof MysqliDriver) {
            error_reporting($errorReporting & ~E_WARNING);
        }

        $db = Database::getInstance();

        $this->assertSame('1', (string) $db->prepare('SELECT 1')->execute(2)->first()->fetchField());
        $this->assertSame('1', (string) $db->prepare('SELECT 1')->execute([2])->first()->fetchField());
        $this->assertSame('1', (string) $db->prepare('SELECT ?')->execute(1, 2)->first()->fetchField());
        $this->assertSame('1', (string) $db->prepare('SELECT ?')->execute([1, 2])->first()->fetchField());
        $this->assertSame('1', (string) $db->prepare('SELECT ?')->execute(1, 2, 3, 4, 5, 6)->first()->fetchField());
        $this->assertSame('1', (string) $db->prepare('SELECT ?')->execute([1, 2, 3, 4, 5, 6])->first()->fetchField());

        // Restore the error reporting level
        error_reporting($errorReporting);

        if ($driver instanceof PdoMysqlDriver) {
            $this->expectExceptionMessageMatches('/number of bound variables does not match number of tokens/i');
            $db->prepare('SELECT ?, ?, ?')->execute(1, 2)->fetchRow();
        }
    }

    /**
     * @group legacy
     */
    public function testFindByNullBackwardsCompatibility(): void
    {
        $GLOBALS['TL_MODELS']['tl_page'] = PageModel::class;

        /** @phpstan-ignore argument.type */
        $this->assertNull(PageModel::findWithDetails(null));
        $this->assertNull(PageModel::findByPk(null));
        $this->assertNull(PageModel::findOneBy('id', null));
        $this->assertNull(PageModel::findBy('id', null));
        $this->assertNull(PageModel::findAll(['column' => 'id', 'value' => null]));

        unset($GLOBALS['TL_MODELS']);
    }
}
