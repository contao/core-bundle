<?php

namespace Contao\CoreBundle\Picker;

use Contao\BackendUser;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractPickerProvider implements PickerProviderInterface
{
    /**
     * @var FactoryInterface
     */
    private $menuFactory;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param FactoryInterface      $menuFactory
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(FactoryInterface $menuFactory, TokenStorageInterface $tokenStorage)
    {
        $this->menuFactory = $menuFactory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function createMenuItem(PickerConfig $config)
    {
        $params = array_merge(
            [
                'popup' => '1',
            ],
            $this->getRouteParameters(),
            ['picker' => base64_encode(json_encode($config->cloneForCurrent($this->getAlias())))]
        );

        return $this->menuFactory->createItem(
            $this->getAlias(),
            [
                'label' => $GLOBALS['TL_LANG']['MSC'][$this->getAlias()] ?: $this->getAlias(),
                'linkAttributes' => ['class' => $this->getLinkClass()],
                'current' => $this->isCurrent($config),
                'route' => 'contao_backend',
                'routeParameters' => $params,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isCurrent(PickerConfig $config)
    {
        return $config->getCurrent() === $this->getAlias();
    }

    /**
     * Returns the back end user object.
     *
     * @throws \RuntimeException
     *
     * @return BackendUser
     */
    protected function getUser()
    {
        if (null === $this->tokenStorage) {
            throw new \RuntimeException('No token storage provided');
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            throw new \RuntimeException('No token provided');
        }

        $user = $token->getUser();

        if (null === $user) {
            throw new \RuntimeException('The token does not contain a user');
        }

        return $user;
    }

    /**
     * Gets link class for picker menu item.
     *
     * @return string
     */
    abstract protected function getLinkClass();

    /**
     * Gets routing parameters for the backend picker.
     *
     * @return array
     */
    abstract protected function getRouteParameters();
}
