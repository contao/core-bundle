<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Widget;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\System;
use Contao\Widget;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provide methods to handle modules of a page layout.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class ModuleWizard extends Widget
{
    /**
     * Submit indicator
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    private $router;

    /**
     * Constructor.
     *
     * @param array|null $arrAttributes
     */
    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);

        $this->db           = System::getContainer()->get('database_connection');
        $this->requestStack = System::getContainer()->get('request_stack');
        $this->twig         = System::getContainer()->get('twig');
        $this->router       = System::getContainer()->get('router');
    }

    /**
     * Generate the widget and return it as string
     *
     * @return string The widget markup
     */
    public function generate()
    {
        $command  = 'cmd_' . $this->strField;
        $value    = $this->orderBySection($this->varValue);

        $this->handleCommand($command, $value);

        return $this->twig->render(
            '@ContaoCore/Widget/be_modulewizard.html.twig',
            [
                'id'            => $this->strId,
                'command'       => $command,
                'currentRecord' => $this->currentRecord,
                'buttons'       => $this->getButtons(),
                'modules'       => $this->getModules(),
                'sections'      => $this->getSectionLabels($this->getSections()),
                'values'        => $value,
            ]
        );
    }

    private function getModules()
    {
        $modules = $this->db->fetchAll(
            "SELECT id, name, type
             FROM tl_module
             WHERE pid=(SELECT pid FROM {$this->strTable} WHERE id=?)
             ORDER BY name",
            [$this->currentRecord],
            [Type::INTEGER]
        );

        foreach ($modules as &$row) {
            $row['type'] = $GLOBALS['TL_LANG']['FMD'][$row['type']][0];
        }

        // First is the articles module
        array_unshift(
            $modules,
            [
                'id'   => 0,
                'name' => $GLOBALS['TL_LANG']['MOD']['article'][0],
                'type' => 'article',
            ]
        );

        return $modules;
    }

    private function getSections()
    {
        static $sections = null;

        if (null !== $sections) {
            return $sections;
        }

        $sections = ['header', 'left', 'right', 'main', 'footer'];

        $custom = $this->db->fetchColumn(
            "SELECT sections FROM {$this->strTable} WHERE id=?",
            [$this->currentRecord],
            0,
            [Type::INTEGER]
        );

        if ('' !== $custom) {
            $sections = array_merge(
                $sections,
                trimsplit(',', $custom)
            );
        }

        return $sections;
    }

    private function getSectionLabels(array $sections)
    {
        $labels = [];

        foreach ($sections as $name) {
            if (isset($GLOBALS['TL_LANG']['COLS'][$name])
                && !is_array($GLOBALS['TL_LANG']['COLS'][$name])
            ) {
                $labels[$name] = $GLOBALS['TL_LANG']['COLS'][$name];
            } else {
                $labels[$name] = $name;
            }
        }

        return $labels;
    }

    /**
     * Orders widget values by layout section.
     *
     * @param array $values
     *
     * @return array
     */
    private function orderBySection($values)
    {
        if (empty($values) || !is_array($values)) {
            return [['mod' => 0, 'col' => 'main']];
        }

        $sections = $this->getSections();
        $sorted   = array_fill_keys($sections, []);
        $result   = [];

        foreach ($values as $row) {
            $sorted[$row['col']][] = $row;
        }

        foreach ($sorted as $row) {
            $result = array_merge($result, $row);
        }

        return $result;
    }

    private function handleCommand($command, array $value)
    {
        $request  = $this->requestStack->getCurrentRequest();
        $widgetId = $request->query->get('id');
        $rowIndex = $request->query->get('cid');

        if (!$request->query->has($command) || $widgetId !== $this->currentRecord || !is_numeric($rowIndex)) {
            return;
        }

        switch ($request->query->get($command)) {
            case 'copy':
                $value = array_duplicate($value, $rowIndex);
                break;

            case 'up':
                $value = array_move_up($value, $rowIndex);
                break;

            case 'down':
                $value = array_move_down($value, $rowIndex);
                break;

            case 'delete':
                $value = array_delete($value, $rowIndex);
                break;
        }

        $this->db->update(
            $this->strTable,
            [$this->strField => serialize($value)],
            ['id' => $this->currentRecord]
        );

        throw new RedirectResponseException(
            $this->router->generate(
                $request->attributes->get('_route'),
                array_diff_key($request->query->all(), ['cid' => '', $command => ''])
            )
        );
    }

    private function getButtons()
    {
        return ['edit', 'copy', 'delete', 'enable', 'drag', 'up', 'down'];
    }
}
