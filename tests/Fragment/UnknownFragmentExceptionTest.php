<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Tests\Fragment;

use Contao\CoreBundle\Fragment\UnknownFragmentException;
use PHPUnit\Framework\TestCase;

class UnknownFragmentExceptionTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $exception = new UnknownFragmentException();

        $this->assertInstanceOf('Contao\CoreBundle\Fragment\UnknownFragmentException', $exception);
    }
}
