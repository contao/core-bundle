<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Table tl_theme
 */
$GLOBALS['TL_DCA']['tl_theme'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_module', 'tl_style_sheet', 'tl_layout', 'tl_image_size'),
		'enableVersioning'            => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		),
		'onload_callback' => array
		(
			array('contao.listener.datacontainer.theme', 'onCheckPermission'),
			array('contao.listener.datacontainer.theme', 'onUpdateStyleSheet')
		),
		'oncopy_callback' => array
		(
			array('contao.listener.datacontainer.theme', 'onScheduleUpdate')
		),
		'onsubmit_callback' => array
		(
			array('contao.listener.datacontainer.theme', 'onScheduleUpdate')
		)
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 2,
			'fields'                  => array('name'),
			'flag'                    => 1,
			'panelLayout'             => 'sort,search,limit'
		),
		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
			'label_callback'          => array('contao.listener.datacontainer.theme', 'onAddPreviewImage')
		),
		'global_operations' => array
		(
			'importTheme' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['importTheme'],
				'href'                => 'key=importTheme',
				'class'               => 'header_theme_import',
				'button_callback'     => array('contao.listener.datacontainer.theme', 'onImportTheme')
			),
			'store' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['store'],
				'href'                => 'key=themeStore',
				'class'               => 'header_store',
				'button_callback'     => array('contao.listener.datacontainer.theme', 'onThemeStore')
			),
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.svg'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.svg',
				'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.svg',
				'attributes'          => 'style="margin-right:3px"'
			),
			'css' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['css'],
				'href'                => 'table=tl_style_sheet',
				'icon'                => 'css.svg',
				'button_callback'     => array('contao.listener.datacontainer.theme', 'onEditCss')
			),
			'modules' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['modules'],
				'href'                => 'table=tl_module',
				'icon'                => 'modules.svg',
				'button_callback'     => array('contao.listener.datacontainer.theme', 'onEditModules')
			),
			'layout' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['layout'],
				'href'                => 'table=tl_layout',
				'icon'                => 'layout.svg',
				'button_callback'     => array('contao.listener.datacontainer.theme', 'onEditLayout')
			),
			'imageSizes' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['imageSizes'],
				'href'                => 'table=tl_image_size',
				'icon'                => 'sizes.svg',
				'button_callback'     => array('contao.listener.datacontainer.theme', 'onEditImageSizes')
			),
			'exportTheme' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_theme']['exportTheme'],
				'href'                => 'key=exportTheme',
				'icon'                => 'theme_export.svg',
				'button_callback'     => array('contao.listener.datacontainer.theme', 'onExportTheme')
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{title_legend},name,author;{config_legend},folders,screenshot,templates;{image_legend:hide},defaultImageDensities;{vars_legend},vars'
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_theme']['name'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'search'                  => true,
			'eval'                    => array('mandatory'=>true, 'unique'=>true, 'decodeEntities'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
			'sql'                     => "varchar(128) NOT NULL default ''"
		),
		'author' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_theme']['author'],
			'inputType'               => 'text',
			'exclude'                 => true,
			'sorting'                 => true,
			'flag'                    => 11,
			'search'                  => true,
			'eval'                    => array('mandatory'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
			'sql'                     => "varchar(128) NOT NULL default ''"
		),
		'folders' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_theme']['folders'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox'),
			'sql'                     => "blob NULL"
		),
		'screenshot' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_theme']['screenshot'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'filesOnly'=>true, 'isGallery'=>true, 'extensions'=>Config::get('validImageTypes')),
			'sql'                     => "binary(16) NULL"
		),
		'templates' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_theme']['templates'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'        => array('tl_theme', 'getTemplateFolders'),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50 clr'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		),
		'vars' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_theme']['vars'],
			'inputType'               => 'keyValueWizard',
			'exclude'                 => true,
			'sql'                     => "text NULL"
		),
		'defaultImageDensities' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_theme']['defaultImageDensities'],
			'inputType'               => 'text',
			'explanation'             => 'imageSizeDensities',
			'exclude'                 => true,
			'eval'                    => array('helpwizard'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
			'sql'                     => "varchar(255) NOT NULL default ''"
		)
	)
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author     Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated since Contao 4.6, to be removed in Contao 5.0
 *             Use the datacontainer.theme listener instead.
 */
class tl_theme extends Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        @trigger_error(
            'Using class tl_theme has been deprecated and will be removed in Contao 5.0. Use the datacontainer.theme listener instead.',
            E_USER_DEPRECATED
        );

        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     * Check permissions to edit the table
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     *
     * @deprecated
     */
    public function checkPermission()
    {
        \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onCheckPermission();
    }


    /**
     * Add an image to each record
     *
     * @param array  $row
     * @param string $label
     *
     * @return string
     *
     * @deprecated
     */
    public function addPreviewImage($row, $label)
    {
        return \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onAddPreviewImage(
            $row,
            $label
        );
    }


    /**
     * Check for modified style sheets and update them if necessary
     *
     * @deprecated
     */
    public function updateStyleSheet()
    {
        \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onUpdateStyleSheet();
    }


    /**
     * Schedule a style sheet update
     *
     * This method is triggered when a single theme or multiple themes are
     * modified (edit/editAll) or duplicated (copy/copyAll).
     *
     * @deprecated
     */
    public function scheduleUpdate()
    {
        \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onScheduleUpdate();
    }


    /**
     * Return all template folders as array
     *
     * @return array
     *
     * @deprecated
     */
    public function getTemplateFolders()
    {
        return \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onGetTemplateFolders();
    }


    /**
     * Return all template folders as array
     *
     * @param string  $path
     * @param integer $level
     *
     * @return array
     *
     * @deprecated
     */
    protected function doGetTemplateFolders($path, $level = 0)
    {
        $return = array();

        foreach (scan(TL_ROOT . '/' . $path) as $file) {
            if (is_dir(TL_ROOT . '/' . $path . '/' . $file)) {
                $return[$path . '/' . $file] = str_repeat(' &nbsp; &nbsp; ', $level) . $file;
                $return                      =
                    array_merge($return, $this->doGetTemplateFolders($path . '/' . $file, $level + 1));
            }
        }

        return $return;
    }


    /**
     * Return the "import theme" link
     *
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $class
     * @param string $attributes
     *
     * @return string
     *
     * @deprecated
     */
    public function importTheme($href, $label, $title, $class, $attributes)
    {
        return \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onImportTheme(
            $href,
            $label,
            $title,
            $class,
            $attributes
        );
    }


    /**
     * Return the theme store link
     *
     * @return string
     *
     * @deprecated
     */
    public function themeStore()
    {
        return \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onThemeStore();
    }


    /**
     * Return the "edit CSS" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     *
     * @deprecated
     */
    public function editCss($row, $href, $label, $title, $icon, $attributes)
    {
        return \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onEditCss(
            $row,
            $href,
            $label,
            $title,
            $icon,
            $attributes
        );
    }


    /**
     * Return the "edit modules" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     *
     * @deprecated
     */
    public function editModules($row, $href, $label, $title, $icon, $attributes)
    {
        return \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onEditModules(
            $row,
            $href,
            $label,
            $title,
            $icon,
            $attributes
        );
    }


    /**
     * Return the "edit page layouts" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     *
     * @deprecated
     */
    public function editLayout($row, $href, $label, $title, $icon, $attributes)
    {
        return \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onEditLayout(
            $row,
            $href,
            $label,
            $title,
            $icon,
            $attributes
        );
    }


    /**
     * Return the "edit image sizes" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     *
     * @deprecated
     */
    public function editImageSizes($row, $href, $label, $title, $icon, $attributes)
    {
        return \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onEditImageSizes(
            $row,
            $href,
            $label,
            $title,
            $icon,
            $attributes
        );
    }


    /**
     * Return the "export theme" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     *
     * @deprecated
     */
    public function exportTheme($row, $href, $label, $title, $icon, $attributes)
    {
        return \Contao\System::getContainer()->get('contao.listener.datacontainer.theme')->onExportTheme(
            $row,
            $href,
            $label,
            $title,
            $icon,
            $attributes
        );
    }
}
