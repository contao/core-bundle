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
     * @param ContaoFrameworkInterface $framework
     * @param RequestStack             $requestStack
     * @param TokenStorageInterface    $tokenStorage
     */
    public function __construct(
        Connection $connection,
        ContaoFrameworkInterface $framework,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage
    ) {
        $this->connection   = $connection;
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
        $uploader = $this->getDefaultUploader();

        if ($uploader === null) {
            return '';
        }

        $csvImport = new CsvImportUtil(
            $this->connection,
            $this->framework,
            $this->requestStack->getCurrentRequest(),
            $uploader
        );

        $csvImport->setCallback(function (array $data, array $row) {
            return array_merge($data, $row);
        });

        if ($csvImport->isFormSubmitted()) {
            $csvImport->run($dc->table, 'listitems', $dc->id);
        }

        return $csvImport->generate($GLOBALS['TL_LANG']['MSC']['lw_import'][0]);
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
        $uploader = $this->getDefaultUploader();

        if ($uploader === null) {
            return '';
        }

        $csvImport = new CsvImportUtil(
            $this->connection,
            $this->framework,
            $this->requestStack->getCurrentRequest(),
            $uploader
        );

        $csvImport->setCallback(function (array $data, array $row) {
            $data[] = [
                'value' => $row[0],
                'label' => $row[1],
            ];

            return $data;
        });

        if ($csvImport->isFormSubmitted()) {
            $csvImport->run($dc->table, 'options', $dc->id);
        }

        return $csvImport->generate($GLOBALS['TL_LANG']['MSC']['ow_import'][0]);
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
        $uploader = $this->getDefaultUploader();

        if ($uploader === null) {
            return '';
        }

        $csvImport = new CsvImportUtil(
            $this->connection,
            $this->framework,
            $this->requestStack->getCurrentRequest(),
            $uploader
        );

        $csvImport->setCallback(function (array $data, array $row) {
            $data[] = $row;

            return $data;
        });

        $csvImport->setSeparators([
            CsvImportUtil::SEPARATOR_COMMA,
            CsvImportUtil::SEPARATOR_SEMICOLON,
            CsvImportUtil::SEPARATOR_TABULATOR,
        ]);

        if ($csvImport->isFormSubmitted()) {
            $csvImport->run($dc->table, 'tableitems', $dc->id);
        }

        return $csvImport->generate($GLOBALS['TL_LANG']['MSC']['tw_import'][0]);
    }

    /**
     * Get the default file uploader
     *
     * @return FileUpload|null
     */
    protected function getDefaultUploader()
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            return null;
        }

        $user = $token->getUser();

        if ($token === null) {
            return null;
        }

        $class = $user->uploader;

        // See #4086 and #7046
        // TODO why support uploaders?
        if (!class_exists($class) || $class === 'DropZone') {
            $class = 'FileUpload';
        }

        return new $class();
    }
}