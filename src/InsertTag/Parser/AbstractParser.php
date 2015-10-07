<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


namespace Contao\CoreBundle\InsertTag\Parser;


use Contao\CoreBundle\InsertTag\InsertTagCollection;
use Contao\CoreBundle\InsertTag\InsertTagFlagCollection;

abstract class AbstractParser
{
    /**
     * @var InsertTagCollection
     */
    protected $insertTagCollection;

    /**
     * @var InsertTagFlagCollection
     */
    protected $insertTagFlagCollection;

    /**
     * Constructor.
     *
     * @param InsertTagCollection     $insertTagCollection
     * @param InsertTagFlagCollection $insertTagFlagCollection
     */
    public function __construct(
        InsertTagCollection $insertTagCollection,
        InsertTagFlagCollection $insertTagFlagCollection
    ) {
        $this->insertTagCollection     = $insertTagCollection;
        $this->insertTagFlagCollection = $insertTagFlagCollection;
    }

    /**
     * Gets the collection of InsertTags to be considered.
     *
     * @return InsertTagCollection
     */
    public function getInsertTagCollection()
    {
        return $this->insertTagCollection;
    }

    /**
     * Gets the collection of InsertTagFlags to be considered.
     *
     * @return InsertTagFlagCollection
     */
    public function getInsertTagFlagCollection()
    {
        return $this->insertTagFlagCollection;
    }

    /**
     * Splits a string into an array containing the InsertTag string and the
     * InsertTagFlags.
     *
     * @param $string
     *
     * @return array
     */
    protected function splitStringIntoTagsAndFlags($string)
    {
        $result = [
            'tagsAndFlags' => [],
            'context'      => [],
        ];
        $tags = preg_split('/\{\{(([^\{\}]*|(?R))*)\}\}/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);

        for ($_rit=0, $_cnt=count($tags); $_rit<$_cnt; $_rit+=3) {
            $result['context'][] = $tags[$_rit];
            $tagName = $tags[$_rit + 1];

            // Skip empty tags
            if ('' === $tagName) {
                continue;
            }

            $chunks = explode('|', $tagName);
            $tag = array_shift($chunks);

            if ('' === $tag) {
                continue;
            }

            $result['tagsAndFlags'][] = [
                'tag'   => $tag,
                'flags' => (array) $chunks,
            ];
        }

        return $result;
    }
}
