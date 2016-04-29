<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Exception\CsvImportErrorException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FileUpload;
use Doctrine\DBAL\Connection;
use Contao\CoreBundle\Util\CsvImportUtil;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Parent class for the CSV import controllers.
 *
 * @author Kamil Kuzminski <https://github.com/qzminski>
 */
abstract class AbstractCsvImportController
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * Constructor.
     *
     * @param Connection               $connection
     * @param ContaoFrameworkInterface $framework
     * @param TokenStorageInterface    $tokenStorage
     */
    public function __construct(
        Connection $connection,
        ContaoFrameworkInterface $framework,
        TokenStorageInterface $tokenStorage
    ) {
        $this->connection   = $connection;
        $this->framework    = $framework;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Set the database connection
     *
     * @param Connection $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Set the Contao framework
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function setFramework($framework)
    {
        $this->framework = $framework;
    }

    /**
     * Set the token storage
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Create the CSV import instance
     *
     * @param Request $request
     *
     * @return CsvImportUtil
     */
    protected function createImportInstance(Request $request)
    {
        return new CsvImportUtil(
            $this->connection,
            $request,
            $this->getFileUploader()
        );
    }

    /**
     * Run the default import routine
     *
     * @param CsvImportUtil $csvImport
     * @param Request       $request
     * @param string        $fieldName
     * @param string        $submitLabel
     *
     * @return Response
     *
     * @throws RedirectResponseException
     * @throws ResponseException
     */
    protected function runDefaultRoutine(CsvImportUtil $csvImport, Request $request, $fieldName, $submitLabel)
    {
        if ($csvImport->isFormSubmitted()) {
            try {
                $csvImport->run($fieldName);
            } catch (CsvImportErrorException $e) {
                // Add an error message and reload the page on import failure
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                throw new RedirectResponseException($request->getRequestUri());
            }

            $response = new RedirectResponse($this->getRefererUrl($request));

            // Set the backend offset cookie
            $response->headers->setCookie(new Cookie('BE_PAGE_OFFSET', 0, 0));

            // Redirect back
            throw new ResponseException($response);
        }

        return new Response($csvImport->generate(
            $this->framework->getAdapter('Contao\Config')->get('maxFileSize'),
            $submitLabel,
            $this->getRefererUrl($request)
        ));
    }

    /**
     * Get the request URL without "key" parameter
     *
     * @param Request $request
     *
     * @return string
     */
    protected function getRefererUrl(Request $request)
    {
        return str_replace('&key='.$request->query->get('key'), '', $request->getRequestUri());
    }

    /**
     * Get the file uploader
     *
     * @return FileUpload
     */
    protected function getFileUploader()
    {
        if (($token = $this->tokenStorage->getToken()) !== null && ($user = $token->getUser()) !== null) {
            $class = $user->uploader;

            // See #4086 and #7046
            if (class_exists($class) && $class !== 'DropZone') {
                return new $class();
            }
        }

        return new FileUpload();
    }
}