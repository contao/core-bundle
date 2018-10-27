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
 * Parent class for objects that can be modules or content elements.
 *
 * @property string $headline
 * @property string $cssID
 * @property string $space
 * @property string $typePrefix
 * @property string $hl
 * @property string $attributes
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
abstract class Hybrid extends Frontend
{

	/**
	 * Key
	 * @var string
	 */
	protected $strKey;

	/**
	 * Table
	 * @var string
	 */
	protected $strTable;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate;

	/**
	 * Column
	 * @var string
	 */
	protected $strColumn;

	/**
	 * Model
	 * @var Model
	 */
	protected $objModel;

	/**
	 * Parent element
	 * @var Model
	 */
	protected $objParent;

	/**
	 * Current record
	 * @var array
	 */
	protected $arrData = array();

	/**
	 * Style array
	 * @var array
	 */
	protected $arrStyle = array();

	/**
	 * Initialize the object
	 *
	 * @param ContentModel|ModuleModel|FormModel $objElement
	 * @param string                             $strColumn
	 */
	public function __construct($objElement, $strColumn='main')
	{
		parent::__construct();

		// Store the parent element (see #4556)
		if ($objElement instanceof Model || $objElement instanceof Model\Collection)
		{
			/** @var ContentModel|ModuleModel|FormModel $objModel */
			$objModel = $objElement;

			if ($objModel instanceof Model\Collection)
			{
				$objModel = $objModel->current();
			}

			$this->objParent = $objModel;
		}

		if ($this->strKey == '' || $this->strTable == '')
		{
			return;
		}

		/** @var Model $strModelClass */
		$strModelClass = \Model::getClassFromTable($this->strTable);

		// Load the model
		if (class_exists($strModelClass))
		{
			$objHybrid = $strModelClass::findByPk($objElement->{$this->strKey});

			if ($objHybrid === null)
			{
				return;
			}

			$this->objModel = $objHybrid;
		}

		// Directly query the database if there is no model class
		else
		{
			$objHybrid = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
										->limit(1)
										->execute($objElement->{$this->strKey});

			if ($objHybrid->numRows < 1)
			{
				return;
			}
		}

		$cssID = array();
		$this->arrData = $objHybrid->row();

		// Get the CSS ID from the parent element (!)
		$this->cssID = \StringUtil::deserialize($objElement->cssID, true);

		// Override the CSS ID (see #305)
		if (!empty($this->cssID[0]))
		{
			$cssID[0] = $this->cssID[0];
		}

		// Merge the CSS classes (see #6011)
		if (!empty($this->cssID[1]))
		{
			$cssID[1] = trim($cssID[1] . ' ' . $this->cssID[1]);
		}

		$this->cssID = $cssID;
		$this->typePrefix = $objElement->typePrefix;

		$arrHeadline = \StringUtil::deserialize($objElement->headline);
		$this->headline = \is_array($arrHeadline) ? $arrHeadline['value'] : $arrHeadline;
		$this->hl = \is_array($arrHeadline) ? $arrHeadline['unit'] : 'h1';
		$this->strColumn = $strColumn;
	}

	/**
	 * Set an object property
	 *
	 * @param string $strKey
	 * @param mixed  $varValue
	 */
	public function __set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;
	}

	/**
	 * Return an object property
	 *
	 * @param string $strKey
	 *
	 * @return mixed
	 */
	public function __get($strKey)
	{
		if (isset($this->arrData[$strKey]))
		{
			return $this->arrData[$strKey];
		}

		return parent::__get($strKey);
	}

	/**
	 * Check whether a property is set
	 *
	 * @param string $strKey
	 *
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		return isset($this->arrData[$strKey]);
	}

	/**
	 * Return the model
	 *
	 * @return Model
	 */
	public function getModel()
	{
		return $this->objModel;
	}

	/**
	 * Return the parent object
	 *
	 * @return object
	 */
	public function getParent()
	{
		return $this->objParent;
	}

	/**
	 * Parse the template
	 *
	 * @return string
	 */
	public function generate()
	{
		if ($this->objParent instanceof ContentModel && TL_MODE == 'FE' && !BE_USER_LOGGED_IN && ($this->objParent->invisible || ($this->objParent->start != '' && $this->objParent->start > time()) || ($this->objParent->stop != '' && $this->objParent->stop < time())))
		{
			return '';
		}

		$this->Template = new \FrontendTemplate($this->strTemplate);
		$this->Template->setData($this->arrData);

		$this->compile();

		$this->Template->style = !empty($this->arrStyle) ? implode(' ', $this->arrStyle) : '';
		$this->Template->cssID = !empty($this->cssID[0]) ? ' id="' . $this->cssID[0] . '"' : '';
		$this->Template->class = trim($this->typePrefix . $this->strKey . ' ' . $this->cssID[1]);

		$this->Template->inColumn = $this->strColumn;

		if ($this->Template->headline == '')
		{
			$this->Template->headline = $this->headline;
		}

		if ($this->Template->hl == '')
		{
			$this->Template->hl = $this->hl;
		}

		if (!empty($this->objParent->classes) && \is_array($this->objParent->classes))
		{
			$this->Template->class .= ' ' . implode(' ', $this->objParent->classes);
		}

		return $this->Template->parse();
	}

	/**
	 * Compile the current element
	 */
	abstract protected function compile();
}

class_alias(Hybrid::class, 'Hybrid');
