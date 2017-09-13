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
use Symfony\Component\Templating\EngineInterface;

/**
 * Class for generating the switch user button.
 */
class SwitchUserButtonGenerator
{
    protected $authorizationChecker;
    protected $router;
    protected $connection;
    protected $twig;
    protected $tokenStorage;

    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RouterInterface               $router
     * @param Connection                    $connection
     * @param EngineInterface               $twig
     * @param TokenStorageInterface         $tokenStorage
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, RouterInterface $router, Connection $connection, EngineInterface $twig, TokenStorageInterface $tokenStorage)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->connection = $connection;
        $this->twig = $twig;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Generate a switch user button and return it as string.
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
        $stmt->bindValue('id', $row['id']);
        $stmt->execute();

        if (0 === $stmt->rowCount()) {
            throw new UserNotFoundException('Invalid user ID'.$row['id']);
        }

        $tokenUser = $this->tokenStorage->getToken()->getUser();
        $user = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($tokenUser->username === $user->username) {
            return '';
        }

        $url = $this->router->generate('contao_backend', [
            '_switch_user' => $user->username,
        ]);

        return $this->twig->render('@ContaoCore/Backend/switch_user.html.twig', [
            'url' => $url,
            'title' => StringUtil::specialchars($title),
            'image' => Image::getHtml($icon, $label),
        ]);
    }
}
