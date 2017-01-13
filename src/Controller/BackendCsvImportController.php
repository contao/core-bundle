<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\FileUpload;
use Contao\Template;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class BackendCsvImportController
{
    const SEPARATOR_COMMA = 'comma';
    const SEPARATOR_LINEBREAK = 'linebreak';
    const SEPARATOR_SEMICOLON = 'semicolon';
    const SEPARATOR_TABULATOR = 'tabulator';

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param Connection               $connection
     * @param RequestStack             $requestStack
     */
    public function __construct(ContaoFrameworkInterface $framework, Connection $connection, RequestStack $requestStack)
    {
        $this->framework = $framework;
        $this->connection = $connection;
        $this->requestStack = $requestStack;
    }

    public function importListWizard(DataContainer $dc)
    {
        return $this->importFromTemplate(
            function ($row) {
                return $row[0];
            },
            $dc->table,
            'listitems',
            $dc->id,
            $GLOBALS['TL_LANG']['MSC']['lw_import'][0],
            true
        );
    }

    public function importTableWizard(DataContainer $dc)
    {
        return $this->importFromTemplate(
            function ($row) {
                return $row;
            },
            $dc->table,
            'tableitems',
            $dc->id,
            $GLOBALS['TL_LANG']['MSC']['tw_import'][0]
        );
    }

    public function importOptionWizard(DataContainer $dc)
    {
        return $this->importFromTemplate(
            function ($row) {
                return [
                    'value' => $row[0],
                    'label' => $row[1],
                    // TODO should we support group and default?
                ];
            },
            $dc->table,
            'options',
            $dc->id,
            $GLOBALS['TL_LANG']['MSC']['ow_import'][0]
        );
    }

    protected function importFromTemplate(
        callable $callback,
        $table,
        $field,
        $id,
        $submitLabel = null,
        $allowLinebreak = false
    ) {
        $this->framework->initialize();

        $request = $this->requestStack->getCurrentRequest();
        $uploader = new FileUpload();
        $template = $this->prepareTemplate($request, $uploader, $allowLinebreak);

        if (null !== $submitLabel) {
            $template->submitLabel = $submitLabel;
        }

        if ($request->request->get('FORM_SUBMIT') === $this->getFormId($request)) {
            try {
                $data = $this->fetchData($uploader, $request->request->get('separator'), $callback);

                $this->connection->update(
                    $table,
                    [$field => serialize($data)],
                    ['id' => $id]
                );

                $response = new RedirectResponse($this->getBackUrl($request));
                $response->headers->setCookie(new Cookie('BE_PAGE_OFFSET', 0, 0));

                return $response;

            } catch (\RuntimeException $e) {
                $request->getSession()->getFlashBag()->add($e->getMessage());
            }
        }

        return new Response($template->parse());
    }

    /**
     * Get the template
     *
     * @param Request    $request
     * @param FileUpload $uploader
     * @param bool       $allowLinebreak
     *
     * @return Template|\stdClass
     */
    private function prepareTemplate(Request $request, FileUpload $uploader, $allowLinebreak = false)
    {
        /** @var BackendTemplate|\stdClass $template */
        $template = new BackendTemplate('be_csv_import');

        $template->formId      = $this->getFormId($request);
        $template->backUrl     = $this->getBackUrl($request);
        $template->action      = $request->getRequestUri();
        $template->fileMaxSize = $this->framework->getAdapter(Config::class)->get('maxFileSize');
        $template->messages    = $request->getSession()->getFlashBag()->all();
        $template->uploader    = $uploader->generateMarkup();
        $template->separators  = $this->getSeparators($allowLinebreak);
        $template->submitLabel = $GLOBALS['TL_LANG']['MSC']['apply'][0];

        return $template;
    }

    private function fetchData(FileUpload $uploader, $separator, callable $callback)
    {
        $data = [];
        $files = $this->getFiles($uploader);
        $delimiter = $this->getDelimiter($separator);

        foreach ($files as $file) {
            $fp = fopen($file, 'rb');

            while (($row = fgetcsv($fp, 0, $delimiter)) !== false) {
                $data[] = $callback($row);
            }
        }

        return $data;
    }

    private function getFormId(Request $request)
    {
        return 'tl_csv_import_'.$request->query->get('key');
    }

    private function getBackUrl(Request $request)
    {
        return str_replace('&key='.$request->query->get('key'), '', $request->getRequestUri());
    }

    private function getSeparators($allowLinebreak = false)
    {
        $separators = [
            self::SEPARATOR_COMMA     => [
                'delimiter' => ',',
                'value'     => self::SEPARATOR_COMMA,
                'label'     => $GLOBALS['TL_LANG']['MSC']['comma'],
            ],
            self::SEPARATOR_SEMICOLON => [
                'delimiter' => ';',
                'value'     => self::SEPARATOR_SEMICOLON,
                'label'     => $GLOBALS['TL_LANG']['MSC']['semicolon'],
            ],
            self::SEPARATOR_TABULATOR => [
                'delimiter' => "\t",
                'value'     => self::SEPARATOR_TABULATOR,
                'label'     => $GLOBALS['TL_LANG']['MSC']['tabulator'],
            ],
        ];

        if ($allowLinebreak) {
            $separators[self::SEPARATOR_LINEBREAK] = [
                'delimiter' => "\n",
                'value'     => self::SEPARATOR_LINEBREAK,
                'label'     => $GLOBALS['TL_LANG']['MSC']['linebreak'],
            ];
        }

        return $separators;
    }

    private function getDelimiter($separator)
    {
        $separators = $this->getSeparators(true);

        if (!isset($separators[$separator])) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['MSC']['separator'][1]);
        }

        return $separators[$separator]['delimiter'];
    }

    private function getFiles(FileUpload $uploader)
    {
        $files = $uploader->uploadTo('system/tmp');

        if (empty($files)) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['ERR']['all_fields']);
        }

        foreach ($files as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            if ('csv' !== $extension) {
                throw new \RuntimeException(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $extension));
            }
        }

        return $files;
    }
}
