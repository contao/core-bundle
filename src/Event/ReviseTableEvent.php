<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Allows to execute logic when a table is revised.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ReviseTableEvent extends Event
{
    /**
     * @var bool
     */
    private $status;

    /**
     * @var string
     */
    private $table;

    /**
     * @var array
     */
    private $newRecordIds;

    /**
     * @var string
     */
    private $parentTable;

    /**
     * @var array
     */
    private $childTables;

    /**
     * Constructor.
     *
     * @param string $table        The table name
     * @param array  $newRecordIds The IDs of the new records
     * @param string $parentTable  The parent tables
     * @param array  $childTables  The child tables
     */
    public function __construct(&$table, &$newRecordIds, &$parentTable, &$childTables)
    {
        $this->table        = &$table;
        $this->newRecordIds = &$newRecordIds;
        $this->parentTable  = &$parentTable;
        $this->childTables  = &$childTables;
    }

    /**
     * Returns the status.
     *
     * @return bool The status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the status.
     *
     * @param bool $status The status
     */
    public function setStatus($status)
    {
        $this->status = (bool) $status;
    }

    /**
     * Returns the table name.
     *
     * @return string The table name
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Sets the table name.
     *
     * @param string $table The table name
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * Returns the IDs of the new records
     *
     * @return array The IDs of the new records
     */
    public function getNewRecordIds()
    {
        return $this->newRecordIds;
    }

    /**
     * Sets the IDs of the new records.
     *
     * @param array $newRecordIds The IDs of the new records
     */
    public function setNewRecordIds(array $newRecordIds)
    {
        $this->newRecordIds = $newRecordIds;
    }

    /**
     * Returns the parent table name.
     *
     * @return string The parent table name
     */
    public function getParentTable()
    {
        return $this->parentTable;
    }

    /**
     * Sets the parent table name.
     *
     * @param string $parentTable The parent table name
     */
    public function setParentTable($parentTable)
    {
        $this->parentTable = $parentTable;
    }

    /**
     * Returns the child table names.
     *
     * @return array The child table names
     */
    public function getChildTables()
    {
        return $this->childTables;
    }

    /**
     * Sets the child table names.
     *
     * @param array $childTables The child table names
     */
    public function setChildTables(array $childTables)
    {
        $this->childTables = $childTables;
    }
}
