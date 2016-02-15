<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Util;

use Contao\BackendTemplate;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\File;
use Contao\FileUpload;
use Contao\Versions;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Provide methods to handle CSV import.
 *
 * @todo - separate the backend layer even more?
 *
 * @author Kamil Kuzminski <https://github.com/qzminski>
 */
class CsvImportUtil
{
    const SEPARATOR_COMMA = ',';
    const SEPARATOR_LINEBREAK = "\n";
    const SEPARATOR_SEMICOLON = ';';
    const SEPARATOR_TABULATOR = "\t";

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var Request
     */
    private $request;

    /**
     * Data callback
     * @var callable
     */
    private $callback;

    /**
     * Available separators
     * @var array
     */
    private $separators = [
        self::SEPARATOR_COMMA,
        self::SEPARATOR_LINEBREAK,
        self::SEPARATOR_SEMICOLON,
        self::SEPARATOR_TABULATOR,
    ];

    /**
     * Template
     * @var BackendTemplate
     */
    private $template;

    /**
     * File uploader
     * @var FileUpload
     */
    private $uploader;

    /**
     * Valid file extensions
     * @var array
     */
    private $fileExtensions = ['csv'];

    /**
     * Upload folder
     * @var string
     */
    private $uploadFolder = 'system/tmp';

    /**
     * Constructor.
     *
     * @param Connection               $connection
     * @param ContaoFrameworkInterface $framework
     * @param Request                  $request
     * @param FileUpload             $uploader
     */
    public function __construct(
        Connection $connection,
        ContaoFrameworkInterface $framework,
        Request $request,
        FileUpload $uploader
    ) {
        $this->connection = $connection;
        $this->framework  = $framework;
        $this->request    = $request;
        $this->uploader   = $uploader;
    }

    /**
     * Generate the CSV import
     *
     * @param string $submitLabel
     *
     * @return string
     *
     * @throws \Exception
     */
    public function generate($submitLabel)
    {
        $uploader = $this->getUploader();

        if ($uploader === null) {
            throw new \Exception('The CSV uploader is not defined');
        }

        $template              = $this->getTemplate();
        $template->formId      = $this->getFormId();
        $template->backUrl     = $this->getRefererUrl();
        $template->action      = $this->request->getRequestUri();
        $template->fileMaxSize = $this->framework->getAdapter('Contao\Config')->get('maxFileSize');
        $template->message     = $this->framework->getAdapter('Contao\Message')->generate();
        $template->uploader    = $uploader->generateMarkup();
        $template->separators  = $this->generateSeparators();
        $template->submitLabel = $submitLabel;

        return $template->parse();
    }

    /**
     * Run the import
     *
     * @param string $table
     * @param string $field
     * @param int    $id
     *
     * @throws \Exception
     * @throws RedirectResponseException
     */
    public function run($table, $field, $id)
    {
        if (!$this->isFormSubmitted()) {
            throw new \Exception('The CSV import form was not submitted');
        }

        $files = $this->getUploadedFiles();

        // Add an error and reload the page if there was no file selected
        if (count($files) === 0) {
            $this->framework->getAdapter('Contao\Message')->addError($GLOBALS['TL_LANG']['ERR']['all_fields']);

            throw new RedirectResponseException($this->request->getRequestUri());
        }

        $this->storeInDatabase($table, $field, $id, $this->getDataFromFiles($files));

        // Set the backend offset cookie
        $this->framework->getAdapter('Contao\System')->setCookie('BE_PAGE_OFFSET', 0, 0);

        // Redirect back
        throw new RedirectResponseException($this->getRefererUrl());
    }

    /**
     * Return true if the form was submitted
     *
     * @return bool
     */
    public function isFormSubmitted()
    {
        return $this->request->request->get('FORM_SUBMIT') === $this->getFormId();
    }

    /**
     * Get the uploaded files
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getUploadedFiles()
    {
        $uploader = $this->getUploader();

        if ($uploader === null) {
            throw new \Exception('The CSV uploader is not defined');
        }

        $files = [];

        foreach ($uploader->uploadTo($this->getUploadFolder()) as $filePath) {
            $file = new File($filePath);

            // Add an error if the file extension is not valid
            if (!in_array($file->extension, $this->getFileExtensions(), true)) {
                $this->framework->getAdapter('Contao\Message')->addError(
                    sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $file->extension)
                );

                continue;
            }

            $files[] = $filePath;
        }

        return $files;
    }

    /**
     * Get the data from uploaded files
     *
     * @throws \Exception
     * @throws RedirectResponseException
     */
    public function getData()
    {
        $files = $this->getUploadedFiles();

        // Add an error and reload the page if there was no file selected
        if (count($files) === 0) {
            $this->framework->getAdapter('Contao\Message')->addError($GLOBALS['TL_LANG']['ERR']['all_fields']);

            throw new RedirectResponseException($this->request->getRequestUri());
        }

        return $this->getDataFromFiles($files);
    }

    /**
     * Store data in the database
     *
     * @param string $table
     * @param string $field
     * @param int    $id
     * @param array  $data
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function storeInDatabase($table, $field, $id, array $data)
    {
        $versions = new Versions($table, $id);
        $versions->create();

        $this->connection->prepare("UPDATE $table SET $field=? WHERE id=?")
            ->execute([serialize($data), $id]);
    }

    /**
     * Get data callback
     *
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Set data callback
     *
     * @param callable $callback
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Get separators
     *
     * @return array
     */
    public function getSeparators()
    {
        return $this->separators;
    }

    /**
     * Set separators
     *
     * @param array $separators
     */
    public function setSeparators(array $separators)
    {
        $this->separators = $separators;
    }

    /**
     * Get the template
     *
     * @return BackendTemplate
     */
    public function getTemplate()
    {
        if ($this->template === null) {
            $this->template = new BackendTemplate('be_csv_import');
        }

        return $this->template;
    }

    /**
     * Set the template
     *
     * @param BackendTemplate $template
     */
    public function setTemplate(BackendTemplate $template)
    {
        $this->template = $template;
    }

    /**
     * Get the uploader
     *
     * @return FileUpload|null
     */
    public function getUploader()
    {
        return $this->uploader;
    }

    /**
     * Set the uploader
     *
     * @param FileUpload $uploader
     */
    public function setUploader(FileUpload $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * Get the valid file extensions
     *
     * @return array
     */
    public function getFileExtensions()
    {
        return $this->fileExtensions;
    }

    /**
     * Set the valid file extensions
     *
     * @param array $fileExtensions
     */
    public function setFileExtensions(array $fileExtensions)
    {
        $this->fileExtensions = $fileExtensions;
    }

    /**
     * Get the upload folder
     *
     * @return string
     */
    public function getUploadFolder()
    {
        return $this->uploadFolder;
    }

    /**
     * Set the upload folder
     *
     * @param string $uploadFolder
     */
    public function setUploadFolder($uploadFolder)
    {
        $this->uploadFolder = $uploadFolder;
    }

    /**
     * Get the form ID
     *
     * @return string
     */
    protected function getFormId()
    {
        return 'tl_csv_import_'.$this->request->query->get('key');
    }

    /**
     * Get the data from uploaded files
     *
     * @param array $files
     *
     * @return array
     */
    private function getDataFromFiles(array $files)
    {
        $data = [];

        foreach ($files as $filePath) {
            $file = new File($filePath);

            while (($row = @fgetcsv($file->handle, null, $this->getSeparator())) !== false) {
                $data = call_user_func($this->callback, $data, $row);
            }
        }

        return $data;
    }

    /**
     * Get the request URL without "key" parameter
     *
     * @return string
     */
    protected function getRefererUrl()
    {
        return str_replace(
            '&key='.$this->request->query->get('key'),
            '',
            $this->request->getRequestUri()
        );
    }

    /**
     * Generate the separators for the template
     *
     * @return array
     */
    protected function generateSeparators()
    {
        $parsed = [];
        $mapper = $this->getSeparatorsMapper();

        foreach ($this->separators as $separator) {
            $parsed[] = [
                'value' => $mapper[$separator],
                'label' => $GLOBALS['TL_LANG']['MSC'][$mapper[$separator]],
            ];
        }

        return $parsed;
    }

    /**
     * Get the separator from the request
     *
     * @return string
     *
     * @throws \Exception
     */
    private function getSeparator()
    {
        $mapper    = $this->getSeparatorsMapper();
        $separator = $this->request->request->get('separator');

        if (($mappedSeparator = array_search($separator, $mapper, true)) === false) {
            throw new \Exception(sprintf('The CSV separator "%s" is invalid', $separator));
        }

        return $mappedSeparator;
    }

    /**
     * Get the separators mapper
     *
     * @return array
     */
    private function getSeparatorsMapper()
    {
        return [
            self::SEPARATOR_COMMA     => 'comma',
            self::SEPARATOR_LINEBREAK => 'linebreak',
            self::SEPARATOR_SEMICOLON => 'semicolon',
            self::SEPARATOR_TABULATOR => 'tabulator',
        ];
    }
}