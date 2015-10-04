<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Twig;

use Contao\Cache;

class HelperExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            'tabindex' => new \Twig_Function_Method($this, 'getTabindex'),
            'tl_lang'  => new \Twig_Function_Method($this, 'getLabel'),
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'contao_helper';
    }

    public function getTabindex()
    {
        $tabindex = 0;

        if (Cache::has('tabindex')) {
            $tabindex = Cache::get('tabindex');
        }

        $tabindex += 1;

        Cache::set('tabindex', $tabindex);

        return $tabindex;
    }

    public function getLabel()
    {
        $keys = func_get_args();

        if (count($keys) < 2) {
            return '';
        }

        $file = $keys[0];

        // Map the key (see #7217)
        switch ($file)
        {
            case 'CNT':
                $file = 'countries';
                break;

            case 'LNG':
                $file = 'languages';
                break;

            case 'MOD':
            case 'FMD':
                $file = 'modules';
                break;

            case 'FFL':
                $file = 'tl_form_field';
                break;

            case 'CACHE':
                $file = 'tl_page';
                break;

            case 'XPL':
                $file = 'explain';
                break;

            case 'XPT':
                $file = 'exception';
                break;

            case 'MSC':
            case 'ERR':
            case 'CTE':
            case 'PTY':
            case 'FOP':
            case 'CHMOD':
            case 'DAYS':
            case 'MONTHS':
            case 'UNITS':
            case 'CONFIRM':
            case 'DP':
            case 'COLS':
                $file = 'default';
                break;
        }

        \System::loadLanguageFile($file);

        if (count($keys) == 2) {
            return $GLOBALS['TL_LANG'][$keys[0]][$keys[1]];
        }

        return $GLOBALS['TL_LANG'][$keys[0]][$keys[1]][$keys[2]];
    }
}
