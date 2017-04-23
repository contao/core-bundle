<?php

namespace Contao\CoreBundle\Security\User;

use Contao\CoreBundle\Exception\UserNotFoundException;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;

class SwitchUserButtonGenerator
{
    protected $authorizationChecker;
    protected $router;
    protected $connection;
    protected $twig;
    protected $tokenStorage;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, RouterInterface $router, Connection $connection, EngineInterface $twig, TokenStorageInterface $tokenStorage)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->router = $router;
        $this->connection = $connection;
        $this->twig = $twig;
        $this->tokenStorage = $tokenStorage;

    }

    public function generateSwitchUserButton($row, $href, $label, $title, $icon)
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            return '';
        }

        $stmt = $this->connection->prepare("SELECT id, username FROM tl_user WHERE id = :id");
        $stmt->bindValue('id', $row['id']);
        $stmt->execute();

        if (0 === $stmt->rowCount()) {
            throw new UserNotFoundException('Invalid user ID' . $row['id']);
        }

        $tokenUser = $this->tokenStorage->getToken()->getUser();
        $user = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($tokenUser->username === $user->username) {
            return '';
        }

        $url = $this->router->generate('contao_backend', [
            '_switch_user' => $user->username
        ]);

        return $this->twig->render('@ContaoCore/Backend/switch_user.html.twig', [
            'url' => $url,
            'title' => StringUtil::specialchars($title),
            'image' => Image::getHtml($icon, $label),
        ]);
    }

    public function generateSwitchUserLogoutButton()
    {
        if ($this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            return $this->router->generate('contao_backend', [
                '_switch_user' => '_exit',
            ]);
        }

        return $this->router->generate('contao_backend_logout');
    }
}
