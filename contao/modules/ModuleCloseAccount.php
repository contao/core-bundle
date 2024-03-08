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
 * Front end module "close account".
 */
class ModuleCloseAccount extends Module
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_closeAccount';

	/**
	 * Display a wildcard in the back end
	 *
	 * @return string
	 */
	public function generate()
	{
		$container = System::getContainer();
		$request = $container->get('request_stack')->getCurrentRequest();

		if ($request && $container->get('contao.routing.scope_matcher')->isBackendRequest($request))
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . $GLOBALS['TL_LANG']['FMD']['closeAccount'][0] . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = StringUtil::specialcharsUrl(System::getContainer()->get('router')->generate('contao_backend', array('do'=>'themes', 'table'=>'tl_module', 'act'=>'edit', 'id'=>$this->id)));

			return $objTemplate->parse();
		}

		// Return if there is no logged-in user
		if (!$container->get('contao.security.token_checker')->hasFrontendUser())
		{
			return '';
		}

		return parent::generate();
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$this->loadDataContainer('tl_member');

		$user = FrontendUser::getInstance();
		$objMember = MemberModel::findById($user->id);

		// Initialize the password widget
		$arrField = $GLOBALS['TL_DCA']['tl_member']['fields']['password'];
		$arrField['name'] = 'password';
		$arrField['eval']['hideInput'] = true;

		$objWidget = new FormText(FormText::getAttributesFromDca($arrField, $arrField['name']));
		$objWidget->currentRecord = $objMember->id;

		$strFormId = 'tl_close_account_' . $this->id;

		// Validate widget
		if (Input::post('FORM_SUBMIT') == $strFormId)
		{
			$objWidget->validate();

			$container = System::getContainer();
			$passwordHasher = $container->get('security.password_hasher_factory')->getPasswordHasher(FrontendUser::class);

			// Validate the password
			if (!$objWidget->hasErrors() && !$passwordHasher->verify($user->password, $objWidget->value))
			{
				$objWidget->value = '';
				$objWidget->addError($GLOBALS['TL_LANG']['ERR']['invalidPass']);
			}

			// Close account
			if (!$objWidget->hasErrors())
			{
				// HOOK: send account ID
				if (isset($GLOBALS['TL_HOOKS']['closeAccount']) && \is_array($GLOBALS['TL_HOOKS']['closeAccount']))
				{
					foreach ($GLOBALS['TL_HOOKS']['closeAccount'] as $callback)
					{
						System::importStatic($callback[0])->{$callback[1]}($user->id, $this->reg_close, $this);
					}
				}

				// Remove the account
				if ($this->reg_close == 'close_delete')
				{
					if ($this->reg_deleteDir && $objMember->assignDir && ($filesModel = FilesModel::findByUuid($objMember->homeDir)))
					{
						$folder = new Folder($filesModel->path);
						$folder->delete();
					}

					$objMember->delete();

					$container->get('monolog.logger.contao.access')->info('User account ID ' . $user->id . ' (' . Idna::decodeEmail($user->email) . ') has been deleted');
				}
				// Deactivate the account
				else
				{
					$objMember->disable = 1;
					$objMember->tstamp = time();
					$objMember->save();

					$container->get('monolog.logger.contao.access')->info('User account ID ' . $user->id . ' (' . Idna::decodeEmail($user->email) . ') has been deactivated');
				}

				// Log out the user (see #93)
				$container->get('security.token_storage')->setToken();
				$container->get('request_stack')->getSession()->invalidate();

				// Check whether there is a jumpTo page
				if ($objJumpTo = PageModel::findById($this->objModel->jumpTo))
				{
					$this->jumpToOrReload($objJumpTo->row());
				}

				$this->reload();
			}
		}

		$this->Template->fields = $objWidget->parse();

		$this->Template->formId = $strFormId;
		$this->Template->slabel = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['closeAccount']);
	}
}
