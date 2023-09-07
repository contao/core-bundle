<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Migration\Version502;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * @internal
 */
class AlwaysForwardMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_page'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_page');

        return !isset($columns['alwaysforward']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement('
            ALTER TABLE tl_page
            ADD alwaysForward tinyint(1) NOT NULL default 0
        ');

        $this->connection->executeStatement("
            UPDATE tl_page
            SET alwaysForward = 1
            WHERE type = 'forward'
        ");

        return $this->createResult(true);
    }
}
