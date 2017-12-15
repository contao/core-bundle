<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\Authentication;

use Contao\FrontendUser;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class FrontendPreviewToken extends AbstractToken
{
    /**
     * @var bool
     */
    private $showUnpublished;

    /**
     * @param FrontendUser|null $user
     * @param bool              $showUnpublished
     */
    public function __construct(?FrontendUser $user, bool $showUnpublished)
    {
        if (null === $user) {
            parent::__construct([]);
            $this->setUser('anon.');
        } else {
            parent::__construct($user->getRoles());
            $this->setUser($user);
        }

        $this->showUnpublished = $showUnpublished;

        parent::setAuthenticated(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function showUnpublished(): bool
    {
        return $this->showUnpublished;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize([$this->showUnpublished, parent::serialize()]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized): void
    {
        [$this->showUnpublished, $parentStr] = unserialize($serialized, ['allowed_classes' => true]);

        parent::unserialize($parentStr);
    }
}
