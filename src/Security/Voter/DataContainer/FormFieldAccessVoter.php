<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Security\Voter\DataContainer;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @internal
 */
class FormFieldAccessVoter extends AbstractDataContainerVoter
{
    use ParentAccessTrait;

    protected function getTable(): string
    {
        return 'tl_form_field';
    }

    protected function hasAccess(TokenInterface $token, CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        return $this->accessDecisionManager->decide($token, [ContaoCorePermissions::USER_CAN_ACCESS_MODULE], 'form')
            && $this->hasAccessToParent($token, ContaoCorePermissions::USER_CAN_EDIT_FORM, $action);
    }
}
