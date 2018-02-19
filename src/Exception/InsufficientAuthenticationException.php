<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Exception;

use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException as BaseInsufficientAuthenticationException;

class InsufficientAuthenticationException extends BaseInsufficientAuthenticationException
{
}
