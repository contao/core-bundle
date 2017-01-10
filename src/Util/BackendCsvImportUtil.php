<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Util;

use Contao\CoreBundle\Exception\CsvImportErrorException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\FileUpload;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Handles the backend CSV import.
 *
 * @author Kamil Kuzminski <https://github.com/qzminski>
 */
class BackendCsvImportUtil
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
     * BackendCsvImportUtil constructor.
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
     * @throws RedirectResponseException
     * @throws ResponseException
     */
    public function importListWizard(DataContainer $dc)
    {
        $request   = $this->getRequestWithDataContainer($dc);
        $csvImport = $this->createImportInstance($request);

        $csvImport->setCallback(
            function (array $data, array $row) {
                return array_merge($data, $row);
            }
        );

        return $this->runDefaultRoutine($csvImport, $request, 'listitems', $GLOBALS['TL_LANG']['MSC']['lw_import'][0]);
    }

    /**
     * Generate the import view for option wizard
     *
     * @param DataContainer $dc
     *
     * @return string
     *
     * @throws RedirectResponseException
     * @throws ResponseException
     */
    public function importOptionWizard(DataContainer $dc)
    {
        $request   = $this->getRequestWithDataContainer($dc);
        $csvImport = $this->createImportInstance($request);

        $csvImport->setCallback(
            function (array $data, array $row) {
                $data[] = [
                    'value' => $row[0],
                    'label' => $row[1],
                ];

                return $data;
            }
        );

        return $this->runDefaultRoutine($csvImport, $request, 'options', $GLOBALS['TL_LANG']['MSC']['ow_import'][0]);
    }

    /**
     * Generate the import view for table wizard
     *
     * @param DataContainer $dc
     *
     * @return string
     *
     * @throws RedirectResponseException
     * @throws ResponseException
     */
    public function importTableWizard(DataContainer $dc)
    {
        $request   = $this->getRequestWithDataContainer($dc);
        $csvImport = $this->createImportInstance($request);

        $csvImport->setCallback(
            function (array $data, array $row) {
                $data[] = $row;

                return $data;
            }
        );

        $csvImport->setSeparators(
            [
                CsvImportUtil::SEPARATOR_COMMA     => [
                    'value' => 'comma',
                    'label' => $GLOBALS['TL_LANG']['MSC']['comma'],
                ],
                CsvImportUtil::SEPARATOR_SEMICOLON => [
                    'value' => 'semicolon',
                    'label' => $GLOBALS['TL_LANG']['MSC']['semicolon'],
                ],
                CsvImportUtil::SEPARATOR_TABULATOR => [
                    'value' => 'tabulator',
                    'label' => $GLOBALS['TL_LANG']['MSC']['tabulator'],
                ],
            ]
        );

        return $this->runDefaultRoutine($csvImport, $request, 'tableitems', $GLOBALS['TL_LANG']['MSC']['tw_import'][0]);
    }

    /**
     * Get the request enhanced with DataContainer data
     *
     * @param DataContainer $dc
     *
     * @return Request
     */
    private function getRequestWithDataContainer(DataContainer $dc)
    {
        $request = $this->requestStack->getCurrentRequest();
        $request->query->add(['table' => $dc->table, 'id' => $dc->id]);

        return $request;
    }

    /**
     * Create the CSV import instance
     *
     * @param Request $request
     *
     * @return CsvImportUtil
     */
    private function createImportInstance(Request $request)
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
     * @return string
     *
     * @throws RedirectResponseException
     * @throws ResponseException
     */
    private function runDefaultRoutine(CsvImportUtil $csvImport, Request $request, $fieldName, $submitLabel)
    {
        if ($csvImport->isFormSubmitted()) {
            try {
                $csvImport->run($fieldName);
            } catch (CsvImportErrorException $e) {
                // Add an error message and reload the page on import failure
                $request->getSession()->getFlashBag()->add('error', $e->getMessage());
                throw new RedirectResponseException($this->getUrlFromRequest($request));
            }

            $response = new RedirectResponse($this->getRefererUrl($request));

            // Set the backend offset cookie
            $response->headers->setCookie(new Cookie('BE_PAGE_OFFSET', 0, 0));

            // Redirect back
            throw new ResponseException($response);
        }

        $response = new Response(
            $csvImport->generate(
                $this->framework->getAdapter('Contao\Config')->get('maxFileSize'),
                $submitLabel,
                $this->getRefererUrl($request)
            )
        );

        return $response->getContent();
    }

    /**
     * Get the URL from request
     *
     * @param Request $request
     *
     * @return string
     */
    private function getUrlFromRequest(Request $request)
    {
        return $request->query->has('url') ? $request->query->get('url') : $request->getRequestUri();
    }

    /**
     * Get the request URL without "key" parameter
     *
     * @param Request $request
     *
     * @return string
     */
    private function getRefererUrl(Request $request)
    {
        return str_replace('&key='.$request->query->get('key'), '', $this->getUrlFromRequest($request));
    }

    /**
     * Get the file uploader
     *
     * @return FileUpload
     */
    private function getFileUploader()
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
