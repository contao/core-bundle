<?php
declare(strict_types=1);

namespace Contao\CoreBundle\DataContainer\Content;

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Input;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Access
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var Connection */
    private $connection;

    /** @var RequestStack */
    private $requestStack;

    /** @var LoggerInterface */
    private $logger;

    /**
     * Access constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param Connection            $connection
     * @param RequestStack          $requestStack
     * @param LoggerInterface       $logger
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Connection $connection,
        RequestStack $requestStack,
        LoggerInterface $logger
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->connection   = $connection;
        $this->requestStack = $requestStack;
        $this->logger       = $logger;
    }

    /**
     * Remove available content elements
     *
     * @param DataContainer $dc
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function filterContentElements(DataContainer $dc): void
    {
        if (null === $user = $this->getUser()) {
            return;
        }

        // Do not filter anything for admins
        if ($user->isAdmin) {
            return;
        }

        $allowedElements = $this->getAllowedElements($user);

        // Build config of effectively allowed elements
        $config              = (array) $GLOBALS['TL_CTE'];
        $allowedElementsKeys = array_flip($allowedElements);
        foreach ($config as $group => $v) {
            $config[$group] = array_intersect_key($config[$group], $allowedElementsKeys);

            if (empty($config[$group])) {
                unset($config[$group]);
            }
        }

        // Handle edge cases
        if (empty($config)) {
            // No content elements possible, disable new elements
            $GLOBALS['TL_DCA']['tl_content']['config']['closed']       = true;
            $GLOBALS['TL_DCA']['tl_content']['config']['notEditable']  = true;
            $GLOBALS['TL_DCA']['tl_content']['config']['notDeletable'] = true;
            unset($GLOBALS['TL_DCA']['tl_content']['list']['global_operations']['all']);

        } elseif (!\in_array($GLOBALS['TL_DCA']['tl_content']['fields']['type']['default'], $allowedElements, true)) {
            // Default element has been hidden
            reset($config);
            $GLOBALS['TL_DCA']['tl_content']['fields']['type']['default'] = @key(@current($config));
            $GLOBALS['TL_DCA']['tl_content']['palettes']['default']       =
                $GLOBALS['TL_DCA']['tl_content']['palettes'][@key(@current($config))];
        }

        if ('' === (string) Input::get('act') || 'select' === Input::get('act')) {
            return;
        }

        // Apply config
        $GLOBALS['TL_CTE'] = $config;

        // Alter session
        if (null !== ($request = $this->requestStack->getCurrentRequest())
            && null !== ($session = $request->getSession())) {

            // Set allowed content element IDs (edit multiple)
            $current = $session->get('CURRENT');
            if (isset($current['IDS'])
                && \is_array($current['IDS'])
                && \count($current['IDS']) > 0) {

                $current['IDS'] =
                    $this->connection
                        ->executeQuery(
                            'SELECT id FROM tl_content WHERE id IN (?) AND type IN (?)',
                            [$current['IDS'], $allowedElements],
                            [Connection::PARAM_INT_ARRAY, Connection::PARAM_INT_ARRAY]
                        )
                        ->fetchAll(\PDO::FETCH_COLUMN);

                $session->set('CURRENT', $current);
            }

            // Set allowed clipboard IDs
            $clipboard = $session->get('CLIPBOARD');
            if (isset($clipboard['tl_content']['id'])
                && \is_array($clipboard['tl_content']['id'])
                && \count($clipboard['tl_content']['id']) > 0) {

                $clipboard['tl_content']['id'] =
                    $this->connection
                        ->executeQuery(
                            'SELECT id FROM tl_content WHERE id IN (?) AND type IN (?) ORDER BY sorting',
                            [$clipboard['tl_content']['id'], $allowedElements],
                            [Connection::PARAM_INT_ARRAY, Connection::PARAM_INT_ARRAY]
                        )
                        ->fetchAll(\PDO::FETCH_COLUMN);
                $session->set('CLIPBOARD', $clipboard);
            }
        }

        if (\in_array(Input::get('act'), array('show', 'create', 'select', 'editAll'), true)
            || ('paste' === \Input::get('act') && 'create' === \Input::get('mode'))
        ) {
            return;
        }

        // Handle attempts of accessing disallowed elements
        $element = $this->connection
            ->executeQuery('SELECT type FROM tl_content WHERE id=?', [$dc->id])
            ->fetch(\PDO::FETCH_OBJ);

        if (false !== $element && !\in_array($element->type, $allowedElements, true)) {
            $this->logger->warning(
                sprintf("Attempt to access restricted content element $element->type"),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ACCESS)]
            );

            Controller::redirect(Environment::get('script') . '?act=error');
        }
    }


    /**
     * Check if a certain content element can be accessed by a certain user.
     *
     * @param string      $elementType
     * @param BackendUser $user
     *
     * @return bool
     */
    public function isAllowedElement(string $elementType, BackendUser $user): bool
    {
        if ($user->isAdmin) {
            return true;
        }

        return \in_array($elementType, $this->getAllowedElements($user), true);
    }

    /**
     * Returns a list of allowed content element types.
     *
     * @param BackendUser $user
     *
     * @return string[]
     */
    private function getAllowedElements(BackendUser $user): array
    {
        $elements = [];
        /** @noinspection PhpUndefinedFieldInspection */
        foreach ((array) deserialize($user->elements, true) as $item) {
            [$module, $elementType] = explode('.', $item, 2);

            if (Input::get('do') === $module) {
                $elements[] = $elementType;
            }
        }
        return $elements;
    }

    /**
     * @return BackendUser|null
     */
    private function getUser(): ?BackendUser
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        return $token->getUser();
    }
}