<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Security\User;

use Contao\CoreBundle\Exception\UserNotFoundException;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SwitchUserButtonGenerator
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RouterInterface               $router
     * @param Connection                    $connection
     * @param TokenStorageInterface         $tokenStorage
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, RouterInterface $router, Connection $connection, TokenStorageInterface $tokenStorage)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->connection = $connection;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Generates the switch user button and returns it as string.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     *
     * @return string
     */
    public function generateSwitchUserButton(array $row, string $href, string $label, string $title, string $icon): string
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            return '';
        }

        $stmt = $this->connection->prepare('SELECT id, username FROM tl_user WHERE id = :id');
        $stmt->bindValue('id', (int) $row['id']);
        $stmt->execute();

        if (0 === $stmt->rowCount()) {
            throw new UserNotFoundException(sprintf('Invalid user ID %s', $row['id']));
        }

        $user = $stmt->fetch(\PDO::FETCH_OBJ);

        /** @var UserInterface $tokenUser */
        $tokenUser = $this->tokenStorage->getToken()->getUser();

        if ($tokenUser->getUsername() === $user->username) {
            return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon));
        }

        $url = $this->router->generate('contao_backend', ['_switch_user' => $user->username]);
        $title = StringUtil::specialchars($title);

        return sprintf('<a href="%s" title="%s">%s</a>', $url, $title, Image::getHtml($icon, $label));
    }
}
