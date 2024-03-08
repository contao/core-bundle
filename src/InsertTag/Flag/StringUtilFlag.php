<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\InsertTag\Flag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTagFlag;
use Contao\CoreBundle\InsertTag\InsertTagFlag;
use Contao\CoreBundle\InsertTag\InsertTagResult;
use Contao\CoreBundle\InsertTag\OutputType;
use Contao\StringUtil;
use Symfony\Component\String\UnicodeString;

class StringUtilFlag
{
    #[AsInsertTagFlag('attr')]
    public function attr(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result
            ->withValue(StringUtil::specialcharsAttribute($result->getValue()))
            ->withOutputType(OutputType::html)
        ;
    }

    #[AsInsertTagFlag('urlattr')]
    public function urlattr(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result
            ->withValue(StringUtil::specialcharsUrl($result->getValue()))
            ->withOutputType(OutputType::html) // Not type URL because HTML entities are encoded here
        ;
    }

    #[AsInsertTagFlag('standardize')]
    public function standardize(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result
            ->withValue(StringUtil::standardize($result->getValue()))
            ->withOutputType(OutputType::text)
        ;
    }

    #[AsInsertTagFlag('ampersand')]
    public function ampersand(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result
            ->withValue(StringUtil::ampersand($result->getValue()))
            // We cannot safely assume HTML here as the method only encodes ampersands
            ->withOutputType(OutputType::html === $result->getOutputType() ? OutputType::html : OutputType::text)
        ;
    }

    #[AsInsertTagFlag('specialchars')]
    public function specialchars(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result
            ->withValue(StringUtil::specialchars($result->getValue()))
            ->withOutputType(OutputType::html)
        ;
    }

    #[AsInsertTagFlag('encode_email')]
    #[AsInsertTagFlag('encodeemail')]
    public function encodeEmail(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result
            ->withValue(StringUtil::encodeEmail($result->getValue()))
            ->withOutputType(OutputType::html === $result->getOutputType() ? OutputType::html : OutputType::text)
        ;
    }

    #[AsInsertTagFlag('strtolower')]
    #[AsInsertTagFlag('utf8_strtolower')]
    public function strtolower(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result->withValue(mb_strtolower($result->getValue()));
    }

    #[AsInsertTagFlag('strtoupper')]
    #[AsInsertTagFlag('utf8_strtoupper')]
    public function strtoupper(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result->withValue(mb_strtoupper($result->getValue()));
    }

    #[AsInsertTagFlag('ucfirst')]
    public function ucfirst(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result->withValue(mb_strtoupper(mb_substr($result->getValue(), 0, 1)).mb_substr($result->getValue(), 1));
    }

    #[AsInsertTagFlag('lcfirst')]
    public function lcfirst(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result->withValue(mb_strtolower(mb_substr($result->getValue(), 0, 1)).mb_substr($result->getValue(), 1));
    }

    #[AsInsertTagFlag('ucwords')]
    public function ucwords(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result->withValue(preg_replace_callback('/(?<=\s|^)./u', static fn ($matches) => mb_strtoupper($matches[0]), $result->getValue()));
    }

    #[AsInsertTagFlag('utf8_romanize')]
    public function utf8Romanize(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result
            ->withValue((new UnicodeString($result->getValue()))->ascii()->toString())
            ->withOutputType(OutputType::html === $result->getOutputType() ? OutputType::html : OutputType::text)
        ;
    }

    #[AsInsertTagFlag('nl2br')]
    public function nl2Br(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result
            ->withValue(preg_replace('/\r?\n/', '<br>', $result->getValue()))
            ->withOutputType(OutputType::html === $result->getOutputType() ? OutputType::html : OutputType::text)
        ;
    }
}
