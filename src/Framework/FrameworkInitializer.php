<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Framework;

use Contao\ClassLoader;
use Contao\Config;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Exception\AjaxRedirectResponseException;
use Contao\CoreBundle\Exception\IncompleteInstallationException;
use Contao\CoreBundle\Exception\InvalidRequestTokenException;
use Contao\Input;
use Contao\RequestToken;
use Contao\System;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Initializes the Contao 3 framework.
 *
 * @author Christian Schiffler <https://github.com/discordier>
 * @author Yanick Witschi <https://github.com/toflar>
 * @author Leo Feyer <https://github.com/leofeyer>
 * @author Dominik Tomasi <https://github.com/dtomasi>
 * @author Andreas Schempp <https://github.com/aschempp>
 *
 * @internal Do not use this class in your code. Use $container->get('contao.framework')->initialize() instead.
 */
class FrameworkInitializer
{
    use ContainerAwareTrait;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var int
     */
    private $errorLevel;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var array
     */
    private $basicClasses = [
        'System',
        'Config',
        'ClassLoader',
        'TemplateLoader',
        'ModuleLoader',
    ];

    /**
     * Constructor.
     *
     * @param RequestStack     $requestStack The request stack
     * @param RouterInterface  $router       The router service
     * @param SessionInterface $session      The session service
     * @param string           $rootDir      The kernel root directory
     * @param int              $errorLevel   The PHP error level
     */
    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        SessionInterface $session,
        $rootDir,
        $errorLevel
    ) {
        $this->router = $router;
        $this->session = $session;
        $this->rootDir = dirname($rootDir);
        $this->errorLevel = $errorLevel;
        $this->requestStack = $requestStack;
    }

    /**
     * Sets the framework.
     *
     * @param ContaoFrameworkInterface|null $framework The framework
     */
    public function setFramework(ContaoFrameworkInterface $framework = null)
    {
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException If the container or the framwork is not set
     */
    public function initialize()
    {
        if (null === $this->container) {
            throw new \LogicException('The service container has not been set.');
        }

        if (null === $this->framework) {
            throw new \LogicException('The Contao framework has not been set.');
        }

        // Set the current request
        $this->request = $this->requestStack->getCurrentRequest();

        $this->setConstants();
        $this->initializeFramework();
    }

    /**
     * Sets the Contao constants.
     */
    private function setConstants()
    {
        if (!defined('TL_MODE')) {
            define('TL_MODE', $this->getMode());
        }

        define('TL_START', microtime(true));
        define('TL_ROOT', $this->rootDir);
        define('TL_REFERER_ID', $this->getRefererId());

        if (!defined('TL_SCRIPT')) {
            define('TL_SCRIPT', $this->getRoute());
        }

        // Define the login status constants in the back end (see #4099, #5279)
        if ($this->container->isScopeActive(ContaoCoreBundle::SCOPE_BACKEND)) {
            define('BE_USER_LOGGED_IN', false);
            define('FE_USER_LOGGED_IN', false);
        }

        // Define the relative path to the installation (see #5339)
        define('TL_PATH', $this->getPath());
    }

    /**
     * Returns the TL_MODE value.
     *
     * @return string|null The TL_MODE value or null
     */
    private function getMode()
    {
        if ($this->container->isScopeActive(ContaoCoreBundle::SCOPE_BACKEND)) {
            return 'BE';
        }

        if ($this->container->isScopeActive(ContaoCoreBundle::SCOPE_FRONTEND)) {
            return 'FE';
        }

        return null;
    }

    /**
     * Returns the referer ID.
     *
     * @return string|null The referer ID or null
     */
    private function getRefererId()
    {
        if (null === $this->request) {
            return null;
        }

        return $this->request->attributes->get('_contao_referer_id', '');
    }

    /**
     * Returns the route.
     *
     * @return string The route
     */
    private function getRoute()
    {
        if (null === $this->request) {
            return 'console';
        }

        $route = $this->router->generate(
            $this->request->attributes->get('_route'),
            $this->request->attributes->get('_route_params')
        );

        return substr($route, strlen($this->request->getBasePath()) + 1);
    }

    /**
     * Returns the base path.
     *
     * @return string|null The base path
     */
    private function getPath()
    {
        if (null === $this->request) {
            return null;
        }

        return $this->request->getBasePath();
    }

    /**
     * Initializes the framework.
     */
    private function initializeFramework()
    {
        // Set the error_reporting level
        error_reporting($this->errorLevel);

        $this->includeHelpers();
        $this->includeBasicClasses();

        // Set the container
        System::setContainer($this->container);

        /** @var Config $config */
        $config = $this->framework->getAdapter('Contao\Config');

        // Preload the configuration (see #5872)
        $config->preload();

        // Register the class loader
        ClassLoader::scanAndRegister();

        $this->initializeLegacySessionAccess();
        $this->setDefaultLanguage();

        // Fully load the configuration
        $config->getInstance();

        $this->validateInstallation();

        Input::initialize();

        $this->setTimezone();
        $this->triggerInitializeSystemHook();
        $this->handleRequestToken();
    }

    /**
     * Includes some helper files.
     */
    private function includeHelpers()
    {
        require __DIR__ . '/../Resources/contao/helper/functions.php';
        require __DIR__ . '/../Resources/contao/config/constants.php';
        require __DIR__ . '/../Resources/contao/helper/interface.php';
        require __DIR__ . '/../Resources/contao/helper/exception.php';
    }

    /**
     * Includes the basic classes required for further processing.
     */
    private function includeBasicClasses()
    {
        foreach ($this->basicClasses as $class) {
            if (!class_exists($class, false)) {
                require_once __DIR__ . '/../Resources/contao/library/Contao/' . $class . '.php';
                class_alias('Contao\\' . $class, $class);
            }
        }
    }

    /**
     * Initializes session access for $_SESSION['FE_DATA'] and $_SESSION['BE_DATA'].
     */
    private function initializeLegacySessionAccess()
    {
        if (!$this->session->isStarted()) {
            return;
        }

        $_SESSION['BE_DATA'] = $this->session->getBag('contao_backend');
        $_SESSION['FE_DATA'] = $this->session->getBag('contao_frontend');
    }

    /**
     * Sets the default language.
     */
    private function setDefaultLanguage()
    {
        $language = 'en';

        if (null !== $this->request) {
            $language = str_replace('_', '-', $this->request->getLocale());
        }

        // Deprecated since Contao 4.0, to be removed in Contao 5.0
        $GLOBALS['TL_LANGUAGE'] = $language;
        $_SESSION['TL_LANGUAGE'] = $language;
    }

    /**
     * Validates the installation.
     *
     * @throws IncompleteInstallationException If the installation has not been completed
     */
    private function validateInstallation()
    {
        if (null === $this->request) {
            return;
        }

        /** @var Config $config */
        $config = $this->framework->getAdapter('Contao\Config');

        // Show the "incomplete installation" message
        if (!$config->isComplete()) {
            throw new IncompleteInstallationException(
                'The installation has not been completed. Open the Contao install tool to continue.'
            );
        }
    }

    /**
     * Sets the time zone.
     */
    private function setTimezone()
    {
        /** @var Config $config */
        $config = $this->framework->getAdapter('Contao\Config');

        $this->iniSet('date.timezone', $config->get('timeZone'));
        date_default_timezone_set($config->get('timeZone'));
    }

    /**
     * Triggers the initializeSystem hook (see #5665).
     */
    private function triggerInitializeSystemHook()
    {
        if (isset($GLOBALS['TL_HOOKS']['initializeSystem']) && is_array($GLOBALS['TL_HOOKS']['initializeSystem'])) {
            foreach ($GLOBALS['TL_HOOKS']['initializeSystem'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}();
            }
        }

        if (file_exists($this->rootDir . '/system/config/initconfig.php')) {
            include $this->rootDir . '/system/config/initconfig.php';
        }
    }

    /**
     * Handles the request token.
     *
     * @throws AjaxRedirectResponseException|InvalidRequestTokenException If the token is invalid
     */
    private function handleRequestToken()
    {
        /** @var RequestToken $requestToken */
        $requestToken = $this->framework->getAdapter('Contao\RequestToken');

        // Deprecated since Contao 4.0, to be removed in Contao 5.0
        if (!defined('REQUEST_TOKEN')) {
            define('REQUEST_TOKEN', $requestToken->get());
        }

        if ($this->canSkipTokenCheck() || $requestToken->validate($this->request->request->get('REQUEST_TOKEN'))) {
            return;
        }

        if ($this->request->isXmlHttpRequest()) {
            throw new AjaxRedirectResponseException($this->router->generate('contao_backend'));
        }

        throw new InvalidRequestTokenException('Invalid request token. Please reload the page and try again.');
    }

    /**
     * Tries to set a php.ini configuration option.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    private function iniSet($key, $value)
    {
        if (function_exists('ini_set')) {
            ini_set($key, $value);
        }
    }

    /**
     * Checks if the token check can be skipped.
     *
     * @return bool True if the token check can be skipped
     */
    private function canSkipTokenCheck()
    {
        return null === $this->request
            || 'POST' !== $this->request->getRealMethod()
            || !$this->request->attributes->has('_token_check')
            || false === $this->request->attributes->get('_token_check')
        ;
    }
}
