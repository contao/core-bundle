<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

/**
 * Extracts DCA information and cache it
 *
 * The class parses the DCA files and stores various extracts like relations
 * in the cache directory. This metadata can then be loaded and used in the
 * application (e.g. the Model classes).
 *
 * Usage:
 *
 *     $user = DcaExtractor::getInstance('tl_user');
 *
 *     if ($user->hasRelations())
 *     {
 *         print_r($user->getRelations());
 *     }
 */
class DcaExtractor extends Controller
{
	/**
	 * Instances
	 * @var DcaExtractor[]
	 */
	protected static $arrInstances = array();

	/**
	 * Table name
	 * @var string
	 */
	protected $strTable;

	/**
	 * Metadata
	 * @var array
	 */
	protected $arrMeta = array();

	/**
	 * Fields
	 * @var array
	 */
	protected $arrFields = array();

	/**
	 * Unique fields
	 * @var array
	 */
	protected $arrUniqueFields = array();

	/**
	 * Keys
	 * @var array
	 */
	protected $arrKeys = array();

	/**
	 * Relations
	 * @var array
	 */
	protected $arrRelations = array();

	/**
	 * Enums
	 * @var array
	 */
	protected $arrEnums = array();

	/**
	 * SQL buffer
	 * @var array
	 */
	protected static $arrSql = array();

	/**
	 * Database table
	 * @var boolean
	 */
	protected $blnIsDbTable = false;

	/**
	 * Load or create the extract
	 *
	 * @param string $strTable The table name
	 *
	 * @throws \Exception If $strTable is empty
	 */
	protected function __construct($strTable)
	{
		if (!$strTable)
		{
			throw new \Exception('The table name must not be empty');
		}

		parent::__construct();

		$this->strTable = $strTable;

		$strFile = System::getContainer()->getParameter('kernel.cache_dir') . '/contao/sql/' . $strTable . '.php';

		// Try to load from cache
		if (file_exists($strFile))
		{
			include $strFile;
		}
		else
		{
			$this->createExtract();
		}
	}

	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final public function __clone()
	{
	}

	/**
	 * Get one object instance per table
	 *
	 * @param string $strTable The table name
	 *
	 * @return DcaExtractor The object instance
	 */
	public static function getInstance($strTable)
	{
		if (!isset(static::$arrInstances[$strTable]))
		{
			static::$arrInstances[$strTable] = new static($strTable);
		}

		return static::$arrInstances[$strTable];
	}

	/**
	 * Return the metadata as array
	 *
	 * @return array The metadata
	 */
	public function getMeta()
	{
		return $this->arrMeta;
	}

	/**
	 * Return true if there is metadata
	 *
	 * @return boolean True if there is metadata
	 */
	public function hasMeta()
	{
		return !empty($this->arrMeta);
	}

	/**
	 * Return the fields as array
	 *
	 * @return array The fields array
	 */
	public function getFields()
	{
		return $this->arrFields;
	}

	/**
	 * Return true if there are fields
	 *
	 * @return boolean True if there are fields
	 */
	public function hasFields()
	{
		return !empty($this->arrFields);
	}

	/**
	 * Return an array of unique columns
	 *
	 * @return array
	 */
	public function getUniqueFields()
	{
		return $this->arrUniqueFields;
	}

	/**
	 * Return true if there are unique fields
	 *
	 * @return boolean True if there are unique fields
	 */
	public function hasUniqueFields()
	{
		return !empty($this->arrUniqueFields);
	}

	/**
	 * Return the keys as array
	 *
	 * @return array The keys array
	 */
	public function getKeys()
	{
		return $this->arrKeys;
	}

	/**
	 * Return true if there are keys
	 *
	 * @return boolean True if there are keys
	 */
	public function hasKeys()
	{
		return !empty($this->arrKeys);
	}

	/**
	 * Return the relations as array
	 *
	 * @return array The relations array
	 */
	public function getRelations()
	{
		return $this->arrRelations;
	}

	/**
	 * Return true if there are relations
	 *
	 * @return boolean True if there are relations
	 */
	public function hasRelations()
	{
		return !empty($this->arrRelations);
	}

	/**
	 * Return the enums as array
	 *
	 * @return array The enums array
	 */
	public function getEnums()
	{
		return $this->arrEnums;
	}

	/**
	 * Return true if the extract relates to a database table
	 *
	 * @return boolean True if the extract relates to a database table
	 */
	public function isDbTable()
	{
		return $this->blnIsDbTable;
	}

	/**
	 * Return an array that can be used by the database installer
	 *
	 * @return array The data array
	 */
	public function getDbInstallerArray()
	{
		$return = array();

		// Fields
		foreach ($this->arrFields as $k=>$v)
		{
			if (\is_array($v))
			{
				if (!isset($v['name']))
				{
					$v['name'] = $k;
				}

				$return['SCHEMA_FIELDS'][$k] = $v;
			}
			else
			{
				$return['TABLE_FIELDS'][$k] = '`' . $k . '` ' . $v;
			}
		}

		$quote = static function ($item) { return '`' . $item . '`'; };

		// Keys
		foreach ($this->arrKeys as $k=>$v)
		{
			// Handle multi-column indexes (see #5556)
			if (str_contains($k, ','))
			{
				$f = array_map($quote, StringUtil::trimsplit(',', $k));
				$k = str_replace(',', '_', $k);
			}
			else
			{
				$f = array($quote($k));
			}

			if ($v == 'primary')
			{
				$k = 'PRIMARY';
				$v = 'PRIMARY KEY  (' . implode(', ', $f) . ')';
			}
			elseif ($v == 'index')
			{
				$v = 'KEY `' . $k . '` (' . implode(', ', $f) . ')';
			}
			else
			{
				$v = strtoupper($v) . ' KEY `' . $k . '` (' . implode(', ', $f) . ')';
			}

			$return['TABLE_CREATE_DEFINITIONS'][$k] = $v;
		}

		$return['TABLE_OPTIONS'] = '';

		// Options
		foreach ($this->arrMeta as $k=>$v)
		{
			if ($k == 'engine')
			{
				$return['TABLE_OPTIONS'] .= ' ENGINE=' . $v;
			}
			elseif ($k == 'charset')
			{
				$return['TABLE_OPTIONS'] .= ' DEFAULT CHARSET=' . $v;
			}
			elseif ($k == 'collate')
			{
				$return['TABLE_OPTIONS'] .= ' COLLATE ' . $v;
			}
		}

		return $return;
	}

	/**
	 * Create the extract from the DCA files
	 */
	protected function createExtract()
	{
		// Load the default language file (see #7202)
		if (empty($GLOBALS['TL_LANG']['MSC']))
		{
			System::loadLanguageFile('default');
		}

		// Load the data container
		$this->loadDataContainer($this->strTable);

		// Return if the table is not defined
		if (!isset($GLOBALS['TL_DCA'][$this->strTable]))
		{
			return;
		}

		// Return if the DC type is "File"
		if (is_a(DataContainer::getDriverForTable($this->strTable), DC_File::class, true))
		{
			return;
		}

		// Return if the DC type is "Folder" and the DC is not database assisted
		if (is_a(DataContainer::getDriverForTable($this->strTable), DC_Folder::class, true) && empty($GLOBALS['TL_DCA'][$this->strTable]['config']['databaseAssisted']))
		{
			return;
		}

		$arrRelations = array();

		// Check whether there are fields (see #4826)
		if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields']))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $field=>$config)
			{
				// Check whether there is a relation (see #6524)
				if (isset($config['relation']))
				{
					$table = null;

					if (isset($config['foreignKey']))
					{
						$table = explode('.', $config['foreignKey'])[0];
					}

					$arrRelations[$field] = array_merge(array('table'=>$table, 'field'=>'id'), $config['relation']);

					// Store the field delimiter if the related IDs are stored in CSV format (see #257)
					if (isset($config['eval']['csv']))
					{
						$arrRelations[$field]['delimiter'] = $config['eval']['csv'];
					}

					// Table name and field name are mandatory
					if (empty($arrRelations[$field]['table']) || empty($arrRelations[$field]['field']))
					{
						throw new \Exception('Incomplete relation defined for ' . $this->strTable . '.' . $field);
					}
				}

				if (isset($config['enum']))
				{
					$this->arrEnums[$field] = $config['enum'];
				}
			}
		}

		$sql = $GLOBALS['TL_DCA'][$this->strTable]['config']['sql'] ?? array();
		$fields = $GLOBALS['TL_DCA'][$this->strTable]['fields'] ?? array();

		// Relations
		if (!empty($arrRelations))
		{
			$this->arrRelations = array();

			foreach ($arrRelations as $field=>$config)
			{
				$this->arrRelations[$field] = array();

				foreach ($config as $k=>$v)
				{
					$this->arrRelations[$field][$k] = $v;
				}
			}
		}

		// Not a database table or no field information
		if (empty($sql) || empty($fields))
		{
			return;
		}

		$params = System::getContainer()->get('database_connection')->getParams();

		// Add the default engine and charset if none is given
		if (empty($sql['engine']))
		{
			$sql['engine'] = $params['defaultTableOptions']['engine'] ?? 'InnoDB';
		}

		if (empty($sql['charset']))
		{
			$sql['charset'] = $params['defaultTableOptions']['charset'] ?? 'utf8mb4';
		}

		if (empty($sql['collate']))
		{
			$sql['collate'] = $params['defaultTableOptions']['collate'] ?? 'utf8mb4_unicode_ci';
		}

		// Meta
		$this->arrMeta = array
		(
			'engine' => $sql['engine'],
			'charset' => $sql['charset'],
			'collate' => $sql['collate']
		);

		// Fields
		$this->arrFields = array();

		// Fields
		foreach ($fields as $field=>$config)
		{
			if (isset($config['sql']))
			{
				$this->arrFields[$field] = $config['sql'];
			}

			if (isset($config['eval']['unique']) && $config['eval']['unique'])
			{
				$this->arrUniqueFields[] = $field;
			}
		}

		// Keys
		if (!empty($sql['keys']) && \is_array($sql['keys']))
		{
			$this->arrKeys = array();

			foreach ($sql['keys'] as $field=>$type)
			{
				$this->arrKeys[$field] = $type;

				if ($type == 'unique')
				{
					$this->arrUniqueFields[] = $field;
				}
			}
		}

		$this->arrUniqueFields = array_unique($this->arrUniqueFields);
		$this->blnIsDbTable = true;
	}
}
