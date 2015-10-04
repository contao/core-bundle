<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Twig;

class ImageExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            'image_html' => new \Twig_Function_Method($this, 'getHtml', ['is_safe' => ['html']]),
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'contao_image';
    }

    /**
     * Generate an image tag and return it as string
     *
     * @param string $src        The image path
     * @param string $alt        An optional alt attribute
     * @param string $attributes A string of other attributes
     *
     * @return string The image HTML tag
     */
    public function getHtml($src, $alt = '', $attributes = '')
    {
        return \Image::getHtml($src, $alt, $attributes);
    }
}
