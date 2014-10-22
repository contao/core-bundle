<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * System configuration
 */
$GLOBALS['TL_DCA']['tl_settings'] =
[

	// Config
	'config' =>
	[
		'dataContainer'               => 'File',
		'closed'                      => true
	],

	// Palettes
	'palettes' =>
	[
		'__selector__'                => ['useSMTP'],
		'default'                     => '{title_legend},websiteTitle;{date_legend},dateFormat,timeFormat,datimFormat,timeZone;{global_legend:hide},adminEmail,characterSet,minifyMarkup,gzipScripts,coreOnlyMode,bypassCache,debugMode,maintenanceMode;{backend_legend:hide},resultsPerPage,maxResultsPerPage,fileSyncExclude,doNotCollapse,staticFiles,staticPlugins;{frontend_legend},urlSuffix,cacheMode,rewriteURL,useAutoItem,addLanguageToUrl,doNotRedirectEmpty,folderUrl,disableAlias;{proxy_legend:hide},proxyServerIps,sslProxyDomain;{privacy_legend:hide},privacyAnonymizeIp,privacyAnonymizeGA;{security_legend},allowedTags,displayErrors,logErrors,disableRefererCheck,disableIpCheck;{files_legend:hide},allowedDownload,validImageTypes,editableFiles,templateFiles,maxImageWidth,jpgQuality,gdMaxImgWidth,gdMaxImgHeight;{uploads_legend:hide},uploadPath,uploadTypes,uploadFields,maxFileSize,imageWidth,imageHeight;{search_legend:hide},enableSearch,indexProtected;{smtp_legend:hide},useSMTP;{modules_legend:hide},inactiveModules;{cron_legend:hide},disableCron;{timeout_legend:hide},undoPeriod,versionPeriod,logPeriod,sessionTimeout,autologin,lockPeriod;{chmod_legend:hide},defaultUser,defaultGroup,defaultChmod;{update_legend:hide},liveUpdateBase'
	],

	// Subpalettes
	'subpalettes' =>
	[
		'useSMTP'                     => 'smtpHost,smtpUser,smtpPass,smtpEnc,smtpPort'
	],

	// Fields
	'fields' =>
	[
		'websiteTitle' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['websiteTitle'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true]
		],
		'dateFormat' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['dateFormat'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'helpwizard'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50'],
			'explanation'             => 'dateFormat'
		],
		'timeFormat' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['timeFormat'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50']
		],
		'datimFormat' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['datimFormat'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50']
		],
		'timeZone' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['timeZone'],
			'inputType'               => 'select',
			'options'                 => System::getTimeZones(),
			'eval'                    => ['chosen'=>true, 'tl_class'=>'w50']
		],
		'adminEmail' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['adminEmail'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'friendly', 'decodeEntities'=>true, 'tl_class'=>'w50']
		],
		'characterSet' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['characterSet'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'alnum', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'coreOnlyMode' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['coreOnlyMode'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'save_callback' =>
			[
				['tl_settings', 'changeCoreOnlyMode']
			]
		],
		'disableCron' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['disableCron'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'minifyMarkup' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['minifyMarkup'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'gzipScripts' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['gzipScripts'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'resultsPerPage' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['resultsPerPage'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'minval'=>1, 'nospace'=>true, 'tl_class'=>'w50']
		],
		'maxResultsPerPage' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['maxResultsPerPage'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'staticFiles' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['MSC']['staticFiles'],
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'url', 'trailingSlash'=>false, 'tl_class'=>'w50'],
			'save_callback' =>
			[
				['tl_settings', 'checkStaticUrl']
			]
		],
		'staticPlugins' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['MSC']['staticPlugins'],
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'url', 'trailingSlash'=>false, 'tl_class'=>'w50'],
			'save_callback' =>
			[
				['tl_settings', 'checkStaticUrl']
			]
		],
		'fileSyncExclude' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['fileSyncExclude'],
			'inputType'               => 'text',
			'eval'                    => ['tl_class'=>'w50']
		],
		'doNotCollapse' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['doNotCollapse'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50 m12']
		],
		'urlSuffix' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['urlSuffix'],
			'inputType'               => 'text',
			'eval'                    => ['nospace'=>'true', 'tl_class'=>'w50']
		],
		'rewriteURL' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['rewriteURL'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'addLanguageToUrl' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['addLanguageToUrl'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'doNotRedirectEmpty' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['doNotRedirectEmpty'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'useAutoItem' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['useAutoItem'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'disableAlias' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['disableAlias'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'folderUrl' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['folderUrl'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'proxyServerIps' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['proxyServerIps'],
			'inputType'               => 'text',
			'eval'                    => ['tl_class'=>'w50']
		],
		'sslProxyDomain' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['sslProxyDomain'],
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'url', 'tl_class'=>'w50']
		],
		'cacheMode' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['cacheMode'],
			'inputType'               => 'select',
			'options'                 => ['both', 'server', 'browser', 'none'],
			'reference'               => &$GLOBALS['TL_LANG']['tl_settings'],
			'eval'                    => ['tl_class'=>'w50']
		],
		'privacyAnonymizeIp' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['privacyAnonymizeIp'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'privacyAnonymizeGA' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['privacyAnonymizeGA'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'disableRefererCheck' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['disableRefererCheck'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'allowedTags' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['allowedTags'],
			'inputType'               => 'text',
			'eval'                    => ['preserveTags'=>true, 'tl_class'=>'long']
		],
		'debugMode' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['debugMode'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'maintenanceMode' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['maintenanceMode'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'bypassCache' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['bypassCache'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'save_callback' =>
			[
				['tl_settings', 'purgeInternalCache']
			]
		],
		'displayErrors' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['displayErrors'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'logErrors' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['logErrors'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'disableIpCheck' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['disableIpCheck'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'allowedDownload' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['allowedDownload'],
			'inputType'               => 'text',
			'eval'                    => ['tl_class'=>'w50']
		],
		'validImageTypes' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['validImageTypes'],
			'inputType'               => 'text',
			'eval'                    => ['tl_class'=>'w50']
		],
		'editableFiles' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['editableFiles'],
			'inputType'               => 'text',
			'eval'                    => ['tl_class'=>'w50']
		],
		'templateFiles' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['templateFiles'],
			'inputType'               => 'text',
			'eval'                    => ['tl_class'=>'w50'],
			'save_callback' =>
			[
				['tl_settings', 'checkTemplateFiles']
			]
		],
		'maxImageWidth' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['maxImageWidth'],
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'jpgQuality' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['jpgQuality'],
			'inputType'               => 'text',
			'eval'                    => ['rgxp'=>'prcnt', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'gdMaxImgWidth' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['gdMaxImgWidth'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'gdMaxImgHeight' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['gdMaxImgHeight'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'uploadPath' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['uploadPath'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'trailingSlash'=>false, 'tl_class'=>'w50'],
			'save_callback' =>
			[
				['tl_settings', 'checkUploadPath']
			]
		],
		'uploadTypes' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['uploadTypes'],
			'inputType'               => 'text',
			'eval'                    => ['tl_class'=>'w50']
		],
		'uploadFields' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['uploadFields'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'maxFileSize' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['maxFileSize'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'imageWidth' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['imageWidth'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'imageHeight' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['imageHeight'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'enableSearch' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['enableSearch'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50']
		],
		'indexProtected' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['indexProtected'],
			'inputType'               => 'checkbox',
			'eval'                    => ['tl_class'=>'w50'],
			'save_callback' =>
			[
				['tl_settings', 'clearSearchIndex']
			]
		],
		'useSMTP' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['useSMTP'],
			'inputType'               => 'checkbox',
			'eval'                    => ['submitOnChange'=>true]
		],
		'smtpHost' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['smtpHost'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'nospace'=>true, 'tl_class'=>'long']
		],
		'smtpUser' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['smtpUser'],
			'inputType'               => 'text',
			'eval'                    => ['decodeEntities'=>true, 'tl_class'=>'w50']
		],
		'smtpPass' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['smtpPass'],
			'inputType'               => 'textStore',
			'eval'                    => ['decodeEntities'=>true, 'tl_class'=>'w50']
		],
		'smtpEnc' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['smtpEnc'],
			'inputType'               => 'select',
			'options'                 => [''=>'-', 'ssl'=>'SSL', 'tls'=>'TLS'],
			'eval'                    => ['tl_class'=>'w50']
		],
		'smtpPort' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['smtpPort'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'inactiveModules' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['inactiveModules'],
			'input_field_callback'    => ['tl_settings', 'disableModules']
		],
		'undoPeriod' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['undoPeriod'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'versionPeriod' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['versionPeriod'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'logPeriod' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['logPeriod'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'sessionTimeout' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['sessionTimeout'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'autologin' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['autologin'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'lockPeriod' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['lockPeriod'],
			'inputType'               => 'text',
			'eval'                    => ['mandatory'=>true, 'rgxp'=>'natural', 'nospace'=>true, 'tl_class'=>'w50']
		],
		'defaultUser' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['defaultUser'],
			'inputType'               => 'select',
			'foreignKey'              => 'tl_user.username',
			'eval'                    => ['chosen'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50']
		],
		'defaultGroup' => [
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['defaultGroup'],
			'inputType'               => 'select',
			'foreignKey'              => 'tl_user_group.name',
			'eval'                    => ['chosen'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50']
		],
		'defaultChmod' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['defaultChmod'],
			'inputType'               => 'chmod',
			'eval'                    => ['tl_class'=>'clr']
		],
		'liveUpdateBase' =>
		[
			'label'                   => &$GLOBALS['TL_LANG']['tl_settings']['liveUpdateBase'],
			'inputType'               => 'text'
		]
	]
];


/**
 * Class tl_settings
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class tl_settings extends Backend
{

	/**
	 * Disable modules
	 * @param Contao\DataContainer
	 * @return string
	 */
	public function disableModules(Contao\DataContainer $dc)
	{
		$arrModules = [];
		$arrFolders = scan(TL_ROOT . '/system/modules');

		// Store all extensions with their status (based on the .skip file)
		foreach ($arrFolders as $strFolder)
		{
			if (substr($strFolder, 0, 1) == '.')
			{
				continue;
			}

			if ($strFolder == 'core' || !is_dir(TL_ROOT . '/system/modules/' . $strFolder))
			{
				continue;
			}

			$arrModules[$strFolder] = !file_exists(TL_ROOT . '/system/modules/' . $strFolder . '/.skip');
		}

		// Enable or disable the modules as requested
		if (Input::post('FORM_SUBMIT') == 'tl_settings')
		{
			$blnPurgeCache = false;
			$arrDisabled = Input::post('inactiveModules');

			if (!is_array($arrDisabled))
			{
				$arrDisabled = [];
			}

			// Check whether a module status has changed
			foreach ($arrModules as $strModule=>$blnActive)
			{
				if (in_array($strModule, $arrDisabled))
				{
					if ($blnActive)
					{
						$blnPurgeCache = System::disableModule($strModule);
					}
				}
				else
				{
					if (!$blnActive)
					{
						$blnPurgeCache = System::enableModule($strModule);
					}
				}
			}

			// Purge the internal cache (see #5016)
			if ($blnPurgeCache)
			{
				$this->import('Automator');
				$this->Automator->purgeInternalCache();
			}
		}

		// Return the form field
		$return = '
<div class="' . $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['tl_class'] . '">
  <fieldset id="ctrl_' . $dc->field . '" class="tl_checkbox_container">
    <legend>' . $GLOBALS['TL_LANG']['tl_settings']['inactiveModules'][0] . '</legend>
    <input type="hidden" name="' . $dc->inputName . '" value="">
    <input type="checkbox" id="check_all_' . $dc->inputName . '" class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this,\'ctrl_' . $dc->inputName . '\')">
    <label for="check_all_' . $dc->inputName . '" style="color:#a6a6a6"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label><br>';

		$i = 0;
		$lng = str_replace('-', '_', $GLOBALS['TL_LANGUAGE']);

		// Render the checkbox and label
		foreach ($arrModules as $strModule=>$blnActive)
		{
			if (!$blnActive)
			{
				$strFile = 'system/modules/' . $strModule . '/languages/' . $lng . '/modules';

				// Load the modules language file of disabled extensions
				if (file_exists(TL_ROOT . '/' . $strFile . '.xlf'))
				{
					static::convertXlfToPhp($strFile . '.xlf', $lng, true);
				}
				elseif (file_exists(TL_ROOT . '/' . $strFile . '.php'))
				{
					include TL_ROOT . '/' . $strFile . '.php';
				}
			}

			$strTitle = (is_array($GLOBALS['TL_LANG']['MOD'][$strModule]) ? $GLOBALS['TL_LANG']['MOD'][$strModule][0] : $GLOBALS['TL_LANG']['MOD'][$strModule]);

			$return .= '
    <input type="checkbox" name="' . $dc->inputName . '[]" id="opt_' . $dc->inputName . '_' . $i . '" class="tl_checkbox" value="' . $strModule . '" onfocus="Backend.getScrollOffset()"' . ($blnActive ? '' : ' checked') . '>
    <label for="opt_' . $dc->inputName . '_' . $i++ . '"><span style="color:#b3b3b3">[' . $strModule . ']</span> ' . $strTitle . '</label><br>';
		}

		// Add the help text
		$return .= '
  </fieldset>' . (Config::get('showHelp') ? '
  <p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['tl_settings'][$dc->field][1] . '</p>' : '') . '
</div>';

		return $return;
	}


	/**
	 * Purge the internal cache when toggling the Contao safe mode
	 * @param mixed
	 * @return mixed
	 */
	public function changeCoreOnlyMode($varValue)
	{
		if ($varValue != Config::get('coreOnlyMode'))
		{
			$this->import('Automator');
			$this->Automator->purgeInternalCache();
		}

		return $varValue;
	}


	/**
	 * Remove protected search results if the feature is being disabled
	 * @param mixed
	 * @return mixed
	 */
	public function clearSearchIndex($varValue)
	{
		if (!$varValue)
		{
			$this->Database->execute("DELETE FROM tl_search WHERE protected=1");
		}

		return $varValue;
	}


	/**
	 * Make sure that "html5" is in the list of valid template
	 * files, so the back end works correctly (see #3398)
	 * @param mixed
	 * @return mixed
	 */
	public function checkTemplateFiles($varValue)
	{
		if (strpos($varValue, 'html5') === false)
		{
			$varValue .= (($varValue != '') ? ',' : '') . 'html5';
		}

		return $varValue;
	}


	/**
	 * Check the upload path
	 * @param mixed
	 * @return mixed
	 * @throws Exception
	 */
	public function checkUploadPath($varValue)
	{
		$varValue = str_replace(['../', '/..', '/.', './', '://'], '', $varValue);

		if ($varValue == '.' || $varValue == '..' || $varValue == '')
		{
			$varValue = 'files';
		}
		elseif (preg_match('@^(assets|contao|plugins|share|system|templates|vendor|web)(/|$)@', $varValue))
		{
			throw new Exception($GLOBALS['TL_LANG']['ERR']['invalidName']);
		}

		return $varValue;
	}


	/**
	 * Check a static URL
	 * @param mixed
	 * @return mixed
	 */
	public function checkStaticUrl($varValue)
	{
		if ($varValue != '')
		{
			$varValue = preg_replace('@https?://@', '', $varValue);
		}

		return $varValue;
	}


	/**
	 * Purge the internal caches
	 * @param mixed
	 * @return mixed
	 */
	public function purgeInternalCache($varValue)
	{
		if ($varValue && $varValue !== Config::get('bypassCache'))
		{
			$this->import('Automator');
			$this->Automator->purgeInternalCache();
		}

		return $varValue;
	}
}
