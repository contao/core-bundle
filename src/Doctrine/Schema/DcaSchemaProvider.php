<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Doctrine\Schema;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database\Installer;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaConfig;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;

class DcaSchemaProvider
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @param ContaoFrameworkInterface $framework
     * @param Registry|null            $doctrine
     */
    public function __construct(ContaoFrameworkInterface $framework, Registry $doctrine = null)
    {
        $this->framework = $framework;
        $this->doctrine = $doctrine;
    }

    /**
     * Creates a schema.
     *
     * @return Schema
     */
    public function createSchema(): Schema
    {
        if (0 !== \count($this->doctrine->getManagerNames())) {
            return $this->createSchemaFromOrm();
        }

        return $this->createSchemaFromDca();
    }

    /**
     * Adds the DCA data to the Doctrine schema.
     *
     * @param Schema $schema
     */
    public function appendToSchema(Schema $schema): void
    {
        $config = $this->getSqlDefinitions();

        foreach ($config as $tableName => $definitions) {
            $table = $schema->createTable($tableName);

            if (isset($definitions['SCHEMA_FIELDS'])) {
                foreach ($definitions['SCHEMA_FIELDS'] as $fieldName => $config) {
                    $options = $config;
                    unset($options['name'], $options['type']);

                    // Use the binary collation if the "case_sensitive" option is set
                    if ($this->isCaseSensitive($config)) {
                        $options['platformOptions']['collation'] = $this->getBinaryCollation($table);
                    }

                    $table->addColumn($config['name'], $config['type'], $options);
                }
            }

            if (isset($definitions['TABLE_FIELDS'])) {
                foreach ($definitions['TABLE_FIELDS'] as $fieldName => $sql) {
                    $this->parseColumnSql($table, $fieldName, substr($sql, \strlen($fieldName) + 3));
                }
            }

            if (isset($definitions['TABLE_CREATE_DEFINITIONS'])) {
                foreach ($definitions['TABLE_CREATE_DEFINITIONS'] as $keyName => $sql) {
                    $this->parseIndexSql($table, $keyName, strtolower($sql));
                }
            }

            if (isset($definitions['TABLE_OPTIONS'])) {
                if (preg_match('/ENGINE=([^ ]+)/i', $definitions['TABLE_OPTIONS'], $match)) {
                    $table->addOption('engine', $match[1]);
                }

                if (preg_match('/DEFAULT CHARSET=([^ ]+)/i', $definitions['TABLE_OPTIONS'], $match)) {
                    $table->addOption('charset', $match[1]);
                }

                if (preg_match('/COLLATE ([^ ]+)/i', $definitions['TABLE_OPTIONS'], $match)) {
                    $table->addOption('collate', $match[1]);
                }
            }

            // The default InnoDB row format before MySQL 5.7.9 is "Compact" but innodb_large_prefix requires "DYNAMIC"
            if ($table->hasOption('engine') && 'InnoDB' === $table->getOption('engine')) {
                $table->addOption('row_format', 'DYNAMIC');
            }
        }
    }

    /**
     * Creates a Schema instance from Doctrine ORM metadata.
     *
     * @return Schema
     */
    private function createSchemaFromOrm(): Schema
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->doctrine->getManager();

        /** @var ClassMetadata[] $metadata */
        $metadata = $manager->getMetadataFactory()->getAllMetadata();

        // Apply the schema filter
        if ($filter = $this->doctrine->getConnection()->getConfiguration()->getFilterSchemaAssetsExpression()) {
            foreach ($metadata as $key => $data) {
                if (!preg_match($filter, $data->getTableName())) {
                    unset($metadata[$key]);
                }
            }
        }

        if (empty($metadata)) {
            return $this->createSchemaFromDca();
        }

        $tool = new SchemaTool($manager);

        return $tool->getSchemaFromMetadata($metadata);
    }

    /**
     * Creates a Schema instance and adds DCA metadata.
     *
     * @return Schema
     */
    private function createSchemaFromDca(): Schema
    {
        $config = new SchemaConfig();
        $params = $this->doctrine->getConnection()->getParams();

        if (isset($params['defaultTableOptions'])) {
            $config->setDefaultTableOptions($params['defaultTableOptions']);
        }

        $schema = new Schema([], [], $config);

        $this->appendToSchema($schema);

        return $schema;
    }

    /**
     * Parses the column definition and adds it to the schema table.
     *
     * @param Table  $table
     * @param string $columnName
     * @param string $sql
     */
    private function parseColumnSql(Table $table, string $columnName, string $sql): void
    {
        [$dbType, $def] = explode(' ', $sql, 2);

        $type = strtok(strtolower($dbType), '(), ');
        $length = (int) strtok('(), ');
        $fixed = false;
        $scale = null;
        $precision = null;
        $default = null;
        $collation = null;

        $this->setLengthAndPrecisionByType($type, $dbType, $length, $scale, $precision, $fixed);

        $connection = $this->doctrine->getConnection();
        $type = $connection->getDatabasePlatform()->getDoctrineTypeMapping($type);

        if (0 === $length) {
            $length = null;
        }

        if (preg_match('/default (\'[^\']*\'|\d+)/i', $def, $match)) {
            $default = trim($match[1], "'");
        }

        if (preg_match('/collate ([^ ]+)/i', $def, $match)) {
            $collation = $match[1];
        }

        // Use the binary collation if the BINARY flag is set (see #1286)
        if (0 === strncasecmp($def, 'binary ', 7)) {
            $collation = $this->getBinaryCollation($table);
        }

        $options = [
            'length' => $length,
            'unsigned' => false !== stripos($def, 'unsigned'),
            'fixed' => $fixed,
            'default' => $default,
            'notnull' => false !== stripos($def, 'not null'),
            'scale' => null,
            'precision' => null,
            'autoincrement' => false !== stripos($def, 'auto_increment'),
            'comment' => null,
        ];

        if (null !== $scale && null !== $precision) {
            $options['scale'] = $scale;
            $options['precision'] = $precision;
        }

        if (null !== $collation) {
            $options['platformOptions'] = ['collation' => $collation];
        }

        $table->addColumn($columnName, $type, $options);
    }

    /**
     * Sets the length, scale, precision and fixed values by field type.
     *
     * @param string   $type
     * @param string   $dbType
     * @param int|null $length
     * @param int|null $scale
     * @param int|null $precision
     * @param bool     $fixed
     */
    private function setLengthAndPrecisionByType(string $type, string $dbType, ?int &$length, ?int &$scale, ?int &$precision, bool &$fixed): void
    {
        switch ($type) {
            case 'char':
            case 'binary':
                $fixed = true;
                break;

            case 'float':
            case 'double':
            case 'real':
            case 'numeric':
            case 'decimal':
                if (preg_match('/[A-Za-z]+\((\d+)\,(\d+)\)/', $dbType, $match)) {
                    $length = null;
                    $precision = $match[1];
                    $scale = $match[2];
                }
                break;

            case 'tinytext':
                $length = MySqlPlatform::LENGTH_LIMIT_TINYTEXT;
                break;

            case 'text':
                $length = MySqlPlatform::LENGTH_LIMIT_TEXT;
                break;

            case 'mediumtext':
                $length = MySqlPlatform::LENGTH_LIMIT_MEDIUMTEXT;
                break;

            case 'tinyblob':
                $length = MySqlPlatform::LENGTH_LIMIT_TINYBLOB;
                break;

            case 'blob':
                $length = MySqlPlatform::LENGTH_LIMIT_BLOB;
                break;

            case 'mediumblob':
                $length = MySqlPlatform::LENGTH_LIMIT_MEDIUMBLOB;
                break;

            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'integer':
            case 'bigint':
            case 'year':
                $length = null;
                break;
        }
    }

    /**
     * Parses the index definition and adds it to the schema table.
     *
     * @param Table  $table
     * @param string $keyName
     * @param string $sql
     */
    private function parseIndexSql(Table $table, string $keyName, string $sql): void
    {
        if ('PRIMARY' === $keyName) {
            if (!preg_match_all('/`([^`]+)`/', $sql, $matches)) {
                throw new \RuntimeException(sprintf('Primary key definition "%s" could not be parsed.', $sql));
            }

            $table->setPrimaryKey($matches[1]);

            return;
        }

        if (!preg_match('/(.*) `([^`]+)` \((.*)\)/', $sql, $matches)) {
            throw new \RuntimeException(sprintf('Key definition "%s" could not be parsed.', $sql));
        }

        $columns = [];
        $flags = [];

        foreach (explode(',', $matches[3]) as $column) {
            preg_match('/`([^`]+)`(\((\d+)\))?/', $column, $cm);

            $column = $cm[1];

            if (isset($cm[3])) {
                $maxlen = $this->getMaximumIndexLength($table, $column);

                if ($cm[3] > $maxlen) {
                    $cm[3] = $maxlen;
                }

                $column .= '('.$cm[3].')';
            }

            $columns[$cm[1]] = $column;
        }

        if (false !== strpos($matches[1], 'unique')) {
            $table->addUniqueIndex($columns, $matches[2]);
        } else {
            if (false !== strpos($matches[1], 'fulltext')) {
                $flags[] = 'fulltext';
            }

            $table->addIndex($columns, $matches[2], $flags);
        }
    }

    /**
     * Returns the SQL definitions from the Contao installer.
     *
     * @return array
     */
    private function getSqlDefinitions(): array
    {
        $this->framework->initialize();

        /** @var Installer $installer */
        $installer = $this->framework->createInstance(Installer::class);
        $sqlTarget = $installer->getFromDca();
        $sqlLegacy = $installer->getFromFile();

        // Manually merge the legacy definitions (see #4766)
        if (!empty($sqlLegacy)) {
            foreach ($sqlLegacy as $table => $categories) {
                foreach ($categories as $category => $fields) {
                    if (\is_array($fields)) {
                        foreach ($fields as $name => $sql) {
                            $sqlTarget[$table][$category][$name] = $sql;
                        }
                    } else {
                        $sqlTarget[$table][$category] = $fields;
                    }
                }
            }
        }

        // Apply the schema filter (see contao/installation-bundle#78)
        if ($filter = $this->doctrine->getConnection()->getConfiguration()->getFilterSchemaAssetsExpression()) {
            foreach (array_keys($sqlTarget) as $key) {
                if (!preg_match($filter, $key)) {
                    unset($sqlTarget[$key]);
                }
            }
        }

        return $sqlTarget;
    }

    /**
     * Returns the maximum index length of a column depending on the collation.
     *
     * @param Table  $table
     * @param string $column
     *
     * @return int
     */
    private function getMaximumIndexLength(Table $table, string $column): int
    {
        $indexLength = $this->getDefaultIndexLength($table);
        $collation = $table->getOption('collate');
        $connection = $this->doctrine->getConnection();

        // Read the table collation if the table exists
        if ($connection->getSchemaManager()->tablesExist([$table->getName()])) {
            $columnOptions = $connection
                ->query(sprintf("SHOW FULL COLUMNS FROM %s LIKE '%s'", $table->getName(), $column))
                ->fetch(\PDO::FETCH_OBJ)
            ;

            $collation = $columnOptions->Collation;
        }

        if (0 === strncmp($collation, 'utf8mb4', 7)) {
            return (int) floor($indexLength / 4);
        }

        return (int) floor($indexLength / 3);
    }

    /**
     * Returns the default index length of a table.
     *
     * @param Table $table
     *
     * @return int
     */
    private function getDefaultIndexLength(Table $table): int
    {
        $connection = $this->doctrine->getConnection();

        $tableOptions = $connection
            ->query(sprintf("SHOW TABLE STATUS LIKE '%s'", $table->getName()))
            ->fetch(\PDO::FETCH_OBJ)
        ;

        if ('InnoDB' !== $tableOptions->Engine) {
            return 1000;
        }

        $largePrefix = $connection
            ->query("SHOW VARIABLES LIKE 'innodb_large_prefix'")
            ->fetch(\PDO::FETCH_OBJ)
        ;

        if (\in_array(strtolower((string) $largePrefix->Value), ['1', 'on'], true)) {
            return 3072;
        }

        return 767;
    }

    /**
     * Checks if a field has the case-sensitive flag.
     *
     * @param array $config
     *
     * @return bool
     */
    private function isCaseSensitive(array $config): bool
    {
        if (!isset($config['customSchemaOptions']['case_sensitive'])) {
            return false;
        }

        return true === $config['customSchemaOptions']['case_sensitive'];
    }

    /**
     * Returns the binary collation depending on the charset.
     *
     * @param Table $table
     *
     * @return string|null
     */
    private function getBinaryCollation(Table $table): ?string
    {
        if (!$table->hasOption('charset')) {
            return null;
        }

        return $table->getOption('charset').'_bin';
    }
}
