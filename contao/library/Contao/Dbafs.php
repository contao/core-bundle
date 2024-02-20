<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Contao\Filter\SyncExclude;
use Symfony\Component\Filesystem\Path;

/**
 * Handles the database assisted file system (DBAFS)
 *
 * The class provides static methods to add, move, copy and delete resources as
 * well as a method to synchronize the file system and the database.
 *
 * Usage:
 *
 *     $file = Dbafs::addResource('files/james-wilson.jpg');
 */
class Dbafs
{
	/**
	 * Synchronize the database
	 * @var array
	 */
	protected static $arrShouldBeSynchronized = array();

	/**
	 * Adds a file or folder with its parent folders
	 *
	 * @param string  $strResource      The path to the file or folder
	 * @param boolean $blnUpdateFolders If true, the parent folders will be updated
	 *
	 * @return FilesModel The files model
	 *
	 * @throws \Exception                If a parent ID entry is missing
	 * @throws \InvalidArgumentException If the resource is outside the upload folder
	 */
	public static function addResource($strResource, $blnUpdateFolders=true)
	{
		self::validateUtf8Path($strResource);

		$uploadPath = Path::normalize(System::getContainer()->getParameter('contao.upload_path'));
		$projectDir = Path::normalize(System::getContainer()->getParameter('kernel.project_dir'));

		// Remove trailing slashes (see #5707)
		if (str_ends_with($strResource, '/'))
		{
			$strResource = substr($strResource, 0, -1);
		}

		// Normalize the path (see #6034)
		$strResource = str_replace(array('\\', '//'), '/', $strResource);

		// The resource does not exist or lies outside the upload directory
		if (!$strResource || !file_exists($projectDir . '/' . $strResource) || !Path::isBasePath($uploadPath, $strResource))
		{
			throw new \InvalidArgumentException("Invalid resource $strResource");
		}

		$objModel = FilesModel::findByPath($strResource);

		// Return the model if it exists already
		if ($objModel !== null)
		{
			$strHash = ($objModel->type == 'folder') ? static::getFolderHash($objModel->path) : (new File($objModel->path))->hash;

			// Update the timestamp and file hash (see #4818, #7828)
			if ($objModel->hash != $strHash)
			{
				$objModel->tstamp = time();
				$objModel->hash   = $strHash;
				$objModel->save();
			}

			return $objModel;
		}

		$arrPaths    = array();
		$arrChunks   = array_filter(explode('/', Path::makeRelative($strResource, $uploadPath)), 'strlen');
		$strPath     = $uploadPath;
		$arrPids     = array($strPath => null);
		$arrUpdate   = array($strResource);
		$objDatabase = Database::getInstance();

		// Build the paths
		while (\count($arrChunks))
		{
			$strPath .= '/' . array_shift($arrChunks);
			$arrPaths[] = $strPath;
		}

		unset($arrChunks);

		$objModels = FilesModel::findMultipleByPaths($arrPaths);

		// Unset the entries in $arrPaths if the DB entry exists
		if ($objModels !== null)
		{
			while ($objModels->next())
			{
				if (($i = array_search($objModels->path, $arrPaths)) !== false)
				{
					unset($arrPaths[$i]);
					$arrPids[$objModels->path] = $objModels->uuid;
				}
			}
		}

		$arrPaths = array_values($arrPaths);

		// If the resource is a folder, also add its contents
		if (is_dir($projectDir . '/' . $strResource))
		{
			/** @var \SplFileInfo[] $objFiles */
			$objFiles = new \RecursiveIteratorIterator(
				new SyncExclude(
					new \RecursiveDirectoryIterator(
						$projectDir . '/' . $strResource,
						\FilesystemIterator::UNIX_PATHS|\FilesystemIterator::FOLLOW_SYMLINKS|\FilesystemIterator::SKIP_DOTS
					)
				),
				\RecursiveIteratorIterator::SELF_FIRST
			);

			// Add the relative path
			foreach ($objFiles as $objFile)
			{
				$strRelpath = StringUtil::stripRootDir($objFile->getPathname());

				if ($objFile->isDir())
				{
					$arrUpdate[] = $strRelpath;
				}

				$arrPaths[] = $strRelpath;
			}
		}

		$objReturn = null;

		// Create the new resources
		foreach ($arrPaths as $strPath)
		{
			if (\in_array(basename($strPath), array('.public', '.nosync')))
			{
				continue;
			}

			$strParent = \dirname($strPath);

			// The parent ID should be in $arrPids
			// Do not use isset() here, because the PID can be null
			if (\array_key_exists($strParent, $arrPids))
			{
				$strPid = $arrPids[$strParent];
			}
			else
			{
				throw new \Exception("No parent entry for $strParent");
			}

			// Create the file or folder
			if (is_file($projectDir . '/' . $strPath))
			{
				$objFile = new File($strPath);

				$objModel = new FilesModel();
				$objModel->pid       = $strPid;
				$objModel->tstamp    = time();
				$objModel->name      = $objFile->name;
				$objModel->type      = 'file';
				$objModel->path      = $objFile->path;
				$objModel->extension = $objFile->extension;
				$objModel->hash      = $objFile->hash;
				$objModel->uuid      = $objDatabase->getUuid();
				$objModel->save();

				$arrPids[$objFile->path] = $objModel->uuid;
			}
			else
			{
				$objFolder = new Folder($strPath);

				$objModel = new FilesModel();
				$objModel->pid       = $strPid;
				$objModel->tstamp    = time();
				$objModel->name      = $objFolder->name;
				$objModel->type      = 'folder';
				$objModel->path      = $objFolder->path;
				$objModel->extension = '';
				$objModel->uuid      = $objDatabase->getUuid();
				$objModel->save();

				$arrPids[$objFolder->path] = $objModel->uuid;
			}

			// Store the model to be returned (see #5979)
			if ($objModel->path == $strResource)
			{
				$objReturn = $objModel;
			}
		}

		// Update the folder hashes from bottom up after all file hashes are set
		foreach (array_reverse($arrPaths) as $strPath)
		{
			if (is_dir($projectDir . '/' . $strPath))
			{
				$objModel = FilesModel::findByPath($strPath);
				$objModel->hash = static::getFolderHash($strPath);
				$objModel->save();
			}
		}

		// Update the folder hashes
		if ($blnUpdateFolders)
		{
			static::updateFolderHashes($arrUpdate);
		}

		return $objReturn;
	}

	/**
	 * Moves a file or folder to a new location
	 *
	 * @param string $strSource      The source path
	 * @param string $strDestination The target path
	 *
	 * @return FilesModel The files model
	 */
	public static function moveResource($strSource, $strDestination)
	{
		self::validateUtf8Path($strSource);
		self::validateUtf8Path($strDestination);

		$objFile = FilesModel::findByPath($strSource);

		// If there is no entry, directly add the destination
		if ($objFile === null)
		{
			$objFile = static::addResource($strDestination);
		}

		$strFolder = \dirname($strDestination);

		// Set the new parent ID
		if ($strFolder == System::getContainer()->getParameter('contao.upload_path'))
		{
			$objFile->pid = null;
		}
		else
		{
			$objFolder = FilesModel::findByPath($strFolder);

			if ($objFolder === null)
			{
				$objFolder = static::addResource($strFolder);
			}

			$objFile->pid = $objFolder->uuid;
		}

		// Save the resource
		$objFile->path = $strDestination;
		$objFile->name = basename($strDestination);
		$objFile->save();

		// Update all child records
		if ($objFile->type == 'folder')
		{
			$objFiles = FilesModel::findMultipleByBasepath($strSource . '/');

			if ($objFiles !== null)
			{
				while ($objFiles->next())
				{
					$objFiles->path = preg_replace('@^' . preg_quote($strSource, '@') . '/@', $strDestination . '/', $objFiles->path);
					$objFiles->save();
				}
			}
		}

		// Update the MD5 hash of the parent folders
		if (($strPath = \dirname($strSource)) != System::getContainer()->getParameter('contao.upload_path'))
		{
			static::updateFolderHashes($strPath);
		}

		if (($strPath = \dirname($strDestination)) != System::getContainer()->getParameter('contao.upload_path'))
		{
			static::updateFolderHashes($strPath);
		}

		return $objFile;
	}

	/**
	 * Copies a file or folder to a new location
	 *
	 * @param string $strSource      The source path
	 * @param string $strDestination The target path
	 *
	 * @return FilesModel The files model
	 */
	public static function copyResource($strSource, $strDestination)
	{
		self::validateUtf8Path($strSource);
		self::validateUtf8Path($strDestination);

		$objDatabase = Database::getInstance();
		$objFile = FilesModel::findByPath($strSource);

		// Add the source entry
		if ($objFile === null)
		{
			$objFile = static::addResource($strSource);
		}

		$strFolder = \dirname($strDestination);
		$objNewFile = clone $objFile->current();

		// Set the new parent ID
		if ($strFolder == System::getContainer()->getParameter('contao.upload_path'))
		{
			$objNewFile->pid = null;
		}
		else
		{
			$objFolder = FilesModel::findByPath($strFolder);

			if ($objFolder === null)
			{
				$objFolder = static::addResource($strFolder);
			}

			$objNewFile->pid = $objFolder->uuid;
		}

		// Save the resource
		$objNewFile->tstamp = time();
		$objNewFile->uuid   = $objDatabase->getUuid();
		$objNewFile->path   = $strDestination;
		$objNewFile->name   = basename($strDestination);
		$objNewFile->save();

		// Update all child records
		if ($objFile->type == 'folder')
		{
			$objFiles = FilesModel::findMultipleByBasepath($strSource . '/');

			if ($objFiles !== null)
			{
				$arrMapper = array();

				while ($objFiles->next())
				{
					$objNew = clone $objFiles->current();

					$objNew->pid    = $arrMapper[$objFiles->pid] ?? $objNewFile->uuid;
					$objNew->tstamp = time();
					$objNew->uuid   = $objDatabase->getUuid();
					$objNew->path   = str_replace($strSource . '/', $strDestination . '/', $objFiles->path);
					$objNew->save();

					$arrMapper[$objFiles->uuid] = $objNew->uuid;
				}
			}
		}

		// Update the MD5 hash of the parent folders
		if (($strPath = \dirname($strSource)) != System::getContainer()->getParameter('contao.upload_path'))
		{
			static::updateFolderHashes($strPath);
		}

		if (($strPath = \dirname($strDestination)) != System::getContainer()->getParameter('contao.upload_path'))
		{
			static::updateFolderHashes($strPath);
		}

		return $objNewFile;
	}

	/**
	 * Removes a file or folder
	 *
	 * @param string $strResource The path to the file or folder
	 */
	public static function deleteResource($strResource)
	{
		self::validateUtf8Path($strResource);

		// Remove the resource
		FilesModel::findByPath($strResource)?->delete();

		// Look for subfolders and files
		$objFiles = FilesModel::findMultipleByBasepath($strResource . '/');

		// Remove subfolders and files as well
		if ($objFiles !== null)
		{
			while ($objFiles->next())
			{
				$objFiles->delete();
			}
		}

		static::updateFolderHashes(\dirname($strResource));

		return null;
	}

	/**
	 * Update the hashes of all parent folders of a resource
	 *
	 * @param mixed $varResource A path or an array of paths to update
	 */
	public static function updateFolderHashes($varResource)
	{
		$arrPaths  = array();

		if (!\is_array($varResource))
		{
			$varResource = array($varResource);
		}

		$projectDir = Path::normalize(System::getContainer()->getParameter('kernel.project_dir'));
		$uploadPath = Path::normalize(System::getContainer()->getParameter('contao.upload_path'));

		foreach ($varResource as $strResource)
		{
			self::validateUtf8Path($strResource);

			$strResource = Path::normalize($strResource);
			$arrChunks   = array_filter(explode('/', Path::makeRelative($strResource, $uploadPath)), 'strlen');
			$strPath     = $uploadPath;

			// Do not check files
			if (is_file($projectDir . '/' . $strResource))
			{
				array_pop($arrChunks);
			}

			// Build the paths
			while (\count($arrChunks))
			{
				$strPath .= '/' . array_shift($arrChunks);
				$arrPaths[] = $strPath;
			}

			unset($arrChunks);
		}

		$arrPaths = array_values(array_unique($arrPaths));

		// Store the hash of each folder
		foreach (array_reverse($arrPaths) as $strPath)
		{
			$objModel  = FilesModel::findByPath($strPath);

			// The DB entry does not yet exist
			if ($objModel === null)
			{
				$objModel = static::addResource($strPath, false);
			}

			$objModel->hash = static::getFolderHash($strPath);
			$objModel->save();
		}
	}

	/**
	 * Synchronize the file system with the database
	 *
	 * @return string The path to the synchronization log file
	 *
	 * @throws \Exception If a parent ID entry is missing
	 */
	public static function syncFiles()
	{
		@ini_set('max_execution_time', 0);

		// Consider the suhosin.memory_limit (see #7035)
		if (\extension_loaded('suhosin'))
		{
			if (($limit = \ini_get('suhosin.memory_limit')) !== '')
			{
				@ini_set('memory_limit', $limit);
			}
		}
		else
		{
			@ini_set('memory_limit', -1);
		}

		$objDatabase = Database::getInstance();

		// Begin atomic database access
		$objDatabase->lockTables(array('tl_files'=>'WRITE'));
		$objDatabase->beginTransaction();

		// Reset the "found" flag
		$objDatabase->executeStatement("UPDATE tl_files SET found=0");

		$projectDir = System::getContainer()->getParameter('kernel.project_dir');

		/** @var \SplFileInfo[] $objFiles */
		$objFiles = new \RecursiveIteratorIterator(
			new SyncExclude(
				new \RecursiveDirectoryIterator(
					$projectDir . '/' . System::getContainer()->getParameter('contao.upload_path'),
					\FilesystemIterator::UNIX_PATHS|\FilesystemIterator::FOLLOW_SYMLINKS|\FilesystemIterator::SKIP_DOTS
				)
			),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		$strLog = 'system/tmp/' . md5(uniqid(mt_rand(), true));

		// Open the log file
		$objLog = new File($strLog);
		$objLog->truncate();

		$arrModels = array();
		$arrFoldersToHash = array();
		$arrFoldersToCompare = array();

		// Create or update the database entries
		foreach ($objFiles as $objFile)
		{
			$strRelpath = StringUtil::stripRootDir($objFile->getPathname());

			if (preg_match('//u', $strRelpath) !== 1)
			{
				$objLog->append("[Malformed UTF-8 filename] $strRelpath");
				continue;
			}

			// Get all subfiles in a single query
			if ($objFile->isDir())
			{
				$objSubfiles = FilesModel::findMultipleFilesByFolder($strRelpath);

				if ($objSubfiles !== null)
				{
					while ($objSubfiles->next())
					{
						$arrModels[$objSubfiles->path] = $objSubfiles->current();
					}
				}
			}

			$objModel = $arrModels[$strRelpath] ?? FilesModel::findByPath($strRelpath);

			if ($objModel === null)
			{
				// Add a log entry
				$objLog->append("[Added] $strRelpath");

				// Get the parent folder
				$strParent = \dirname($strRelpath);

				// Get the parent ID
				if ($strParent == System::getContainer()->getParameter('contao.upload_path'))
				{
					$strPid = null;
				}
				else
				{
					$objParent = FilesModel::findByPath($strParent);

					if ($objParent === null)
					{
						throw new \Exception("No parent entry for $strParent");
					}

					$strPid = $objParent->uuid;
				}

				// Create the file or folder
				if (is_file($projectDir . '/' . $strRelpath))
				{
					$objFile = new File($strRelpath);

					$objModel = new FilesModel();
					$objModel->pid       = $strPid;
					$objModel->tstamp    = time();
					$objModel->name      = $objFile->name;
					$objModel->type      = 'file';
					$objModel->path      = $objFile->path;
					$objModel->extension = $objFile->extension;
					$objModel->found     = 2;
					$objModel->hash      = $objFile->hash;
					$objModel->uuid      = $objDatabase->getUuid();
					$objModel->save();
				}
				else
				{
					$objFolder = new Folder($strRelpath);

					$objModel = new FilesModel();
					$objModel->pid       = $strPid;
					$objModel->tstamp    = time();
					$objModel->name      = $objFolder->name;
					$objModel->type      = 'folder';
					$objModel->path      = $objFolder->path;
					$objModel->extension = '';
					$objModel->found     = 2;
					$objModel->uuid      = $objDatabase->getUuid();
					$objModel->save();

					$arrFoldersToHash[] = $strRelpath;
				}
			}
			elseif ($objFile->isDir())
			{
				$arrFoldersToCompare[] = $objModel;
			}
			else
			{
				// Check whether the MD5 hash has changed
				$strHash = (new File($strRelpath))->hash;
				$strType = ($objModel->hash != $strHash) ? 'Changed' : 'Unchanged';

				// Add a log entry
				$objLog->append("[$strType] $strRelpath");

				// Update the record
				$objModel->found = 1;
				$objModel->hash  = $strHash;
				$objModel->save();
			}
		}

		// Update the folder hashes from bottom up after all file hashes are set
		foreach (array_reverse($arrFoldersToHash) as $strPath)
		{
			$objModel = FilesModel::findByPath($strPath);
			$objModel->hash = static::getFolderHash($strPath);
			$objModel->save();
		}

		// Compare the folders after all hashes are set
		foreach (array_reverse($arrFoldersToCompare) as $objModel)
		{
			// Check whether the MD5 hash has changed
			$strHash = static::getFolderHash($objModel->path);
			$strType = ($objModel->hash != $strHash) ? 'Changed' : 'Unchanged';

			// Add a log entry
			$objLog->append("[$strType] $objModel->path");

			// Update the record
			$objModel->found = 1;
			$objModel->hash  = $strHash;
			$objModel->save();
		}

		// Check for left-over entries in the DB
		$objFiles = FilesModel::findByFound('');

		if ($objFiles !== null)
		{
			$arrMapped = array();
			$arrPidUpdate = array();

			while ($objFiles->next())
			{
				$objFound = FilesModel::findBy(array('hash=?', 'found=2'), $objFiles->hash);

				if ($objFound !== null)
				{
					// Check for matching file names if the result is ambiguous (see #5644)
					if ($objFound->count() > 1)
					{
						while ($objFound->next())
						{
							if ($objFound->name == $objFiles->name)
							{
								$objFound = $objFound->current();
								break;
							}
						}
					}

					// If another file has been mapped already, delete the entry (see #6008)
					if (\in_array($objFound->path, $arrMapped))
					{
						$objLog->append("[Deleted] $objFiles->path");
						$objFiles->delete();
						continue;
					}

					$arrMapped[] = $objFound->path;

					// Store the PID change
					if ($objFiles->type == 'folder')
					{
						$arrPidUpdate[$objFound->uuid] = $objFiles->uuid;
					}

					// Add a log entry BEFORE changing the object
					$objLog->append("[Moved] $objFiles->path to $objFound->path");

					// Update the original entry
					$objFiles->pid    = $objFound->pid;
					$objFiles->tstamp = $objFound->tstamp;
					$objFiles->name   = $objFound->name;
					$objFiles->type   = $objFound->type;
					$objFiles->path   = $objFound->path;
					$objFiles->found  = 1;

					// Delete the newer (duplicate) entry
					$objFound->delete();

					// Then save the modified original entry (prevents duplicate key errors)
					$objFiles->save();
				}
				else
				{
					// Add a log entry BEFORE changing the object
					$objLog->append("[Deleted] $objFiles->path");

					// Delete the entry if the resource has gone
					$objFiles->delete();
				}
			}

			// Update the PID of the child records
			if (!empty($arrPidUpdate))
			{
				foreach ($arrPidUpdate as $from=>$to)
				{
					$objChildren = FilesModel::findByPid($from);

					if ($objChildren !== null)
					{
						while ($objChildren->next())
						{
							$objChildren->pid = $to;
							$objChildren->save();
						}
					}
				}
			}
		}

		// Close the log file
		$objLog->close();

		// Reset the found flag
		$objDatabase->executeStatement("UPDATE tl_files SET found=1 WHERE found=2");

		// Finalize database access
		$objDatabase->commitTransaction();
		$objDatabase->unlockTables();

		// Return the path to the log file
		return $strLog;
	}

	/**
	 * Get the folder hash from the database by combining the hashes of all children
	 *
	 * @param string $strPath The relative path
	 *
	 * @return string MD5 hash
	 */
	public static function getFolderHash($strPath)
	{
		self::validateUtf8Path($strPath);

		$strPath = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $strPath);
		$arrHash = array();

		$objChildren = Database::getInstance()
			->prepare("SELECT hash, name FROM tl_files WHERE path LIKE ? AND path NOT LIKE ? ORDER BY name")
			->execute($strPath . '/%', $strPath . '/%/%')
		;

		if ($objChildren !== null)
		{
			while ($objChildren->next())
			{
				$arrHash[] = $objChildren->hash . $objChildren->name;
			}
		}

		return md5(implode("\0", $arrHash));
	}

	/**
	 * Check if the current resource should be synchronized with the database
	 *
	 * @param string $strPath The relative path
	 *
	 * @return bool True if the current resource needs to be synchronized with the database
	 */
	public static function shouldBeSynchronized($strPath)
	{
		if (!isset(static::$arrShouldBeSynchronized[$strPath]) || !\is_bool(static::$arrShouldBeSynchronized[$strPath]))
		{
			static::$arrShouldBeSynchronized[$strPath] = !static::isFileSyncExclude($strPath);
		}

		return static::$arrShouldBeSynchronized[$strPath];
	}

	/**
	 * Check if a file or folder is excluded from synchronization
	 *
	 * @param string $strPath The relative path
	 *
	 * @return bool True if the file or folder is excluded from synchronization
	 */
	protected static function isFileSyncExclude($strPath)
	{
		self::validateUtf8Path($strPath);

		$projectDir = System::getContainer()->getParameter('kernel.project_dir');

		// Look for an existing parent folder (see #410)
		while ($strPath != '.' && !is_dir($projectDir . '/' . $strPath))
		{
			$strPath = \dirname($strPath);
		}

		if ($strPath == '.')
		{
			return true;
		}

		$uploadPath = System::getContainer()->getParameter('contao.upload_path');

		// Outside the files directory
		if (!Path::isBasePath($uploadPath, $strPath))
		{
			return true;
		}

		return (new Folder($strPath))->isUnsynchronized();
	}

	private static function validateUtf8Path($strPath)
	{
		if (preg_match('//u', $strPath) !== 1)
		{
			throw new \InvalidArgumentException(sprintf('Path "%s" contains malformed UTF-8 characters.', $strPath));
		}
	}
}
