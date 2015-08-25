<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\InsertTag\Parser;

use Symfony\Component\HttpFoundation\Request;

class StringParser extends AbstractParser implements ParserInterface
{
    /**
     * Applies the current InsertTags and InsertTagFlags on a given string.
     *
     * @param         $string
     * @param Request $request
     *
     * @return string
     */
    public function parse($string, Request $request)
    {
        $buffer = '';
        $data = $this->splitStringIntoTagsAndFlags($string);

        foreach ($data['tagsAndFlags'] as $i => $tagAndFlags) {

            $buffer .= $data['context'][$i];
            $tag     = $tagAndFlags['tag'];
            $flags   = $tagAndFlags['flags'];

            // Recursive replace
            if (strpos($tag, '{{') !== false) {
                $tag = $this->parse($tag, $request);
            }

            // Loop over InsertTags
            foreach ($this->getInsertTagCollection()->all() as $insertTag) {
                if (!$insertTag->isResponsible($tag, $request)) {

                    continue;
                }

                // Apply InsertTag
                $response = $insertTag->getResponse($tag, $request);

                // Loop over InsertTagFlags
                foreach ($this->getInsertTagFlagCollection()->all() as $insertTagFlag) {
                    foreach ($flags as $flag) {
                        if (!$insertTagFlag->isResponsible($flag, $request)) {

                            continue;
                        }

                        $insertTagFlag->getResponse($response, $request);
                    }
                }

                $buffer .= $response->getContent();
            }
        }

        $buffer .= end($data['context']);

        return $buffer;
    }
}
