<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Adapter;

/**
 * Provides an adapter for the Contao Dbafs class.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class DbafsAdapter implements DbafsAdapterInterface
{
    /**
     * Adds a file or folder with its parent folders
     *
     * @param string  $strResource      The path to the file or folder
     * @param boolean $blnUpdateFolders If true, the parent folders will be updated
     *
     * @return \FilesModel The files model
     *
     * @throws \Exception                If a parent ID entry is missing
     * @throws \InvalidArgumentException If the resource is outside the upload folder
     */
    public function addResource($strResource, $blnUpdateFolders = true)
    {
        return \Contao\Dbafs::addResource($strResource, $blnUpdateFolders);
    }

    /**
     * Moves a file or folder to a new location
     *
     * @param string $strSource      The source path
     * @param string $strDestination The target path
     *
     * @return \FilesModel The files model
     */
    public function moveResource($strSource, $strDestination)
    {
        return \Contao\Dbafs::moveResource($strSource, $strDestination);
    }

    /**
     * Copies a file or folder to a new location
     *
     * @param string $strSource      The source path
     * @param string $strDestination The target path
     *
     * @return \FilesModel The files model
     */
    public function copyResource($strSource, $strDestination)
    {
        return \Contao\Dbafs::copyResource($strSource, $strDestination);
    }

    /**
     * Removes a file or folder
     *
     * @param string $strResource The path to the file or folder
     */
    public function deleteResource($strResource)
    {
        \Contao\Dbafs::deleteResource($strResource);
    }

    /**
     * Update the hashes of all parent folders of a resource
     *
     * @param mixed $varResource A path or an array of paths to update
     */
    public function updateFolderHashes($varResource)
    {
        \Contao\Dbafs::updateFolderHashes($varResource);
    }

    /**
     * Synchronize the file system with the database
     *
     * @return string The path to the synchronization log file
     *
     * @throws \Exception If a parent ID entry is missing
     */
    public function syncFiles()
    {
        return \Contao\Dbafs::syncFiles();
    }
}
