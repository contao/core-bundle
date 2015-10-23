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
     * @var bool
     */
    private $reload = false;

    /**
     * Constructor.
     *
     * @param string $table        The table name
     * @param array  $newRecordIds The IDs of the new records
     * @param string $parentTable  The parent tables
     * @param array  $childTables  The child tables
     */
    public function __construct($table, $newRecordIds, $parentTable, $childTables)
    {
        $this->table = $table;
        $this->newRecordIds = $newRecordIds;
        $this->parentTable = $parentTable;
        $this->childTables = $childTables;
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
     * Returns the IDs of the new records.
     *
     * @return array The IDs of the new records
     */
    public function getNewRecordIds()
    {
        return $this->newRecordIds;
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
     * Returns the child table names.
     *
     * @return array The child table names
     */
    public function getChildTables()
    {
        return $this->childTables;
    }

    /**
     * Returns the reload status.
     *
     * @return bool The reload status
     */
    public function isReload()
    {
        return $this->reload;
    }

    /**
     * Sets the reload status.
     *
     * @param bool $reload The reload status
     */
    public function setReload($reload)
    {
        $this->reload = (bool) $reload;
    }
}
