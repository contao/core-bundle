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
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Util\CsvImportUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the backend CSV import.
 *
 * @author Kamil Kuzminski <https://github.com/qzminski>
 */
class BackendCsvImportController extends AbstractCsvImportController
{
    /**
     * Generate the import view for list wizard
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws RedirectResponseException
     * @throws ResponseException
     */
    public function importListWizardAction(Request $request)
    {
        $csvImport = $this->createImportInstance($request);

        $csvImport->setCallback(function (array $data, array $row) {
            return array_merge($data, $row);
        });

        return $this->runDefaultRoutine($csvImport, $request, 'listitems', $GLOBALS['TL_LANG']['MSC']['lw_import'][0]);
    }

    /**
     * Generate the import view for option wizard
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws RedirectResponseException
     * @throws ResponseException
     */
    public function importOptionWizardAction(Request $request)
    {
        $csvImport = $this->createImportInstance($request);

        $csvImport->setCallback(function (array $data, array $row) {
            $data[] = [
                'value' => $row[0],
                'label' => $row[1],
            ];

            return $data;
        });

        return $this->runDefaultRoutine($csvImport, $request, 'options', $GLOBALS['TL_LANG']['MSC']['ow_import'][0]);
    }

    /**
     * Generate the import view for table wizard
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws RedirectResponseException
     * @throws ResponseException
     */
    public function importTableWizardAction(Request $request)
    {
        $csvImport = $this->createImportInstance($request);

        $csvImport->setCallback(function (array $data, array $row) {
            $data[] = $row;

            return $data;
        });

        $csvImport->setSeparators([
            CsvImportUtil::SEPARATOR_COMMA,
            CsvImportUtil::SEPARATOR_SEMICOLON,
            CsvImportUtil::SEPARATOR_TABULATOR,
        ]);

        return $this->runDefaultRoutine($csvImport, $request, 'tableitems', $GLOBALS['TL_LANG']['MSC']['tw_import'][0]);
    }
}