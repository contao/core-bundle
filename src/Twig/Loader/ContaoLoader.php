<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Twig\Loader;

/**
 * ContaoLoader
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class ContaoLoader extends \Twig_Loader_Filesystem
{
    const BUNDLE_NAMESPACE = '__bundle__';
    const THEME_NAMESPACE  = '__theme__';
    const LOCAL_NAMESPACE  = '__local__';

    public function __construct(array $paths = [])
    {
        parent::__construct($paths);

        if ($paths) {
            $this->setPaths($paths, self::BUNDLE_NAMESPACE);
        }
    }

    public function setThemePaths(array $paths)
    {
        $this->setPaths($paths, self::THEME_NAMESPACE);

        $this->setPaths(
            array_merge(
                $this->getPaths(self::LOCAL_NAMESPACE),
                $this->getPaths(self::THEME_NAMESPACE),
                $this->getPaths(self::BUNDLE_NAMESPACE)
            )
        );
    }
}
