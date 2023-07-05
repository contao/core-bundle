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
use Contao\System;

class FormatFlag
{
    #[AsInsertTagFlag('number_format')]
    public function numberFormat(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result
            ->withValue(System::getFormattedNumber((float) $result->getValue(), 0))
            ->withOutputType(OutputType::text)
        ;
    }

    #[AsInsertTagFlag('currency_format')]
    public function currencyFormat(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result
            ->withValue(System::getFormattedNumber((float) $result->getValue()))
            ->withOutputType(OutputType::text)
        ;
    }

    #[AsInsertTagFlag('readable_size')]
    public function readableSize(InsertTagFlag $flag, InsertTagResult $result): InsertTagResult
    {
        return $result
            ->withValue(System::getReadableSize((int) $result->getValue()))
            ->withOutputType(OutputType::text)
        ;
    }
}
