<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\FileUpload;
use Doctrine\DBAL\Connection;
use Contao\CoreBundle\Util\CsvImportUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Handles the backend CSV import.
 *
 * @author Kamil Kuzminski <https://github.com/qzminski>
 */
class BackendCsvImportController
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param Connection               $connection
     * @param FlashBagInterface        $flashBag
     * @param ContaoFrameworkInterface $framework
     * @param RequestStack             $requestStack
     * @param TokenStorageInterface    $tokenStorage
     */
    public function __construct(
        Connection $connection,
        FlashBagInterface $flashBag,
        ContaoFrameworkInterface $framework,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage
    ) {
        $this->connection   = $connection;
        $this->flashBag     = $flashBag;
        $this->framework    = $framework;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Generate the import view for list wizard
     *
     * @param DataContainer $dc
     *
     * @return string
     *
     * @throws \Exception
     * @throws RedirectResponseException
     */
    public function importListWizard(DataContainer $dc)
    {
        if (($csvImport = $this->createImportInstance()) === null) {
            return '';
        }

        $csvImport->setCallback(function (array $data, array $row) {
            return array_merge($data, $row);
        });

        // Run the import upon form submit
        if ($csvImport->isFormSubmitted()) {
            $csvImport->run($dc->table, 'listitems', $dc->id);

            // Set the backend offset cookie
            $this->framework->getAdapter('Contao\System')->setCookie('BE_PAGE_OFFSET', 0, 0);
        }

        return $csvImport->generate(
            $this->framework->getAdapter('Contao\Config')->get('maxFileSize'),
            $GLOBALS['TL_LANG']['MSC']['lw_import'][0]
        );
    }

    /**
     * Generate the import view for option wizard
     *
     * @param DataContainer $dc
     *
     * @return string
     *
     * @throws \Exception
     * @throws RedirectResponseException
     */
    public function importOptionWizard(DataContainer $dc)
    {
        if (($csvImport = $this->createImportInstance()) === null) {
            return '';
        }

        $csvImport->setCallback(function (array $data, array $row) {
            $data[] = [
                'value' => $row[0],
                'label' => $row[1],
            ];

            return $data;
        });

        // Run the import upon form submit
        if ($csvImport->isFormSubmitted()) {
            $csvImport->run($dc->table, 'options', $dc->id);

            // Set the backend offset cookie
            $this->framework->getAdapter('Contao\System')->setCookie('BE_PAGE_OFFSET', 0, 0);
        }

        return $csvImport->generate(
            $this->framework->getAdapter('Contao\Config')->get('maxFileSize'),
            $GLOBALS['TL_LANG']['MSC']['ow_import'][0]
        );
    }

    /**
     * Generate the import view for table wizard
     *
     * @param DataContainer $dc
     *
     * @return string
     *
     * @throws \Exception
     * @throws RedirectResponseException
     */
    public function importTableWizard(DataContainer $dc)
    {
        if (($csvImport = $this->createImportInstance()) === null) {
            return '';
        }

        $csvImport->setCallback(function (array $data, array $row) {
            $data[] = $row;

            return $data;
        });

        $csvImport->setSeparators([
            CsvImportUtil::SEPARATOR_COMMA,
            CsvImportUtil::SEPARATOR_SEMICOLON,
            CsvImportUtil::SEPARATOR_TABULATOR,
        ]);

        // Run the import upon form submit
        if ($csvImport->isFormSubmitted()) {
            $csvImport->run($dc->table, 'tableitems', $dc->id);

            // Set the backend offset cookie
            $this->framework->getAdapter('Contao\System')->setCookie('BE_PAGE_OFFSET', 0, 0);
        }

        return $csvImport->generate(
            $this->framework->getAdapter('Contao\Config')->get('maxFileSize'),
            $GLOBALS['TL_LANG']['MSC']['tw_import'][0]
        );
    }

    /**
     * Create the CSV import instance
     *
     * @return CsvImportUtil|null
     */
    private function createImportInstance()
    {
        if (($uploader = $this->getDefaultUploader()) === null) {
            return null;
        }

        return new CsvImportUtil(
            $this->connection,
            $this->flashBag,
            $this->requestStack->getCurrentRequest(),
            $uploader
        );
    }

    /**
     * Get the default file uploader
     *
     * @return FileUpload|null
     */
    protected function getDefaultUploader()
    {
        if (($token = $this->tokenStorage->getToken()) === null) {
            return null;
        }

        if (($user = $token->getUser()) === null) {
            return null;
        }

        $class = $user->uploader;

        // See #4086 and #7046
        if (!class_exists($class) || $class === 'DropZone') {
            $class = 'FileUpload';
        }

        return new $class();
    }
}