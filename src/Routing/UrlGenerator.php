<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Routing;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var bool
     */
    private $prependLocale;

    public function __construct(UrlGeneratorInterface $router, ContaoFrameworkInterface $framework, bool $prependLocale)
    {
        $this->router = $router;
        $this->framework = $framework;
        $this->prependLocale = $prependLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context): void
    {
        $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): RequestContext
    {
        return $this->router->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH): ?string
    {
        $this->framework->initialize();

        if (!\is_array($parameters)) {
            $parameters = [];
        }

        $context = $this->getContext();

        // Store the original request context
        $host = $context->getHost();
        $scheme = $context->getScheme();
        $httpPort = $context->getHttpPort();
        $httpsPort = $context->getHttpsPort();

        $this->prepareLocale($parameters);
        $this->prepareAlias($name, $parameters);
        $this->prepareDomain($context, $parameters, $referenceType);

        unset($parameters['auto_item']);

        $url = $this->router->generate(
            'index' === $name ? 'contao_index' : 'contao_frontend',
            $parameters,
            $referenceType
        );

        // Reset the request context
        $context->setHost($host);
        $context->setScheme($scheme);
        $context->setHttpPort($httpPort);
        $context->setHttpsPort($httpsPort);

        return $url;
    }

    /**
     * Removes the locale parameter if it is disabled.
     */
    private function prepareLocale(array &$parameters): void
    {
        if (!$this->prependLocale && array_key_exists('_locale', $parameters)) {
            unset($parameters['_locale']);
        }
    }

    /**
     * Adds the parameters to the alias.
     *
     * @throws MissingMandatoryParametersException
     */
    private function prepareAlias(string $alias, array &$parameters): void
    {
        if ('index' === $alias) {
            return;
        }

        $hasAutoItem = false;
        $autoItems = $this->getAutoItems($parameters);

        /** @var Config $config */
        $config = $this->framework->getAdapter(Config::class);

        $parameters['alias'] = preg_replace_callback(
            '/\{([^\}]+)\}/',
            function (array $matches) use ($alias, &$parameters, $autoItems, &$hasAutoItem, $config): string {
                $param = $matches[1];

                if (!isset($parameters[$param])) {
                    throw new MissingMandatoryParametersException(
                        sprintf('Parameters "%s" is missing to generate a URL for "%s"', $param, $alias)
                    );
                }

                $value = $parameters[$param];
                unset($parameters[$param]);

                if ($hasAutoItem || !$config->get('useAutoItem') || !\in_array($param, $autoItems, true)) {
                    return $param.'/'.$value;
                }

                $hasAutoItem = true;

                return $value;
            },
            $alias
        );
    }

    /**
     * Forces the router to add the host if necessary.
     */
    private function prepareDomain(RequestContext $context, array &$parameters, int &$referenceType): void
    {
        if (isset($parameters['_ssl'])) {
            $context->setScheme(true === $parameters['_ssl'] ? 'https' : 'http');
        }

        if (isset($parameters['_domain']) && '' !== $parameters['_domain']) {
            $this->addHostToContext($context, $parameters, $referenceType);
        }

        unset($parameters['_domain'], $parameters['_ssl']);
    }

    /**
     * Sets the context from the domain.
     */
    private function addHostToContext(RequestContext $context, array $parameters, int &$referenceType): void
    {
        [$host, $port] = $this->getHostAndPort($parameters['_domain']);

        if ($context->getHost() === $host) {
            return;
        }

        $context->setHost($host);
        $referenceType = UrlGeneratorInterface::ABSOLUTE_URL;

        if (!$port) {
            return;
        }

        if (isset($parameters['_ssl']) && true === $parameters['_ssl']) {
            $context->setHttpsPort($port);
        } else {
            $context->setHttpPort($port);
        }
    }

    /**
     * Extracts host and port from the domain.
     *
     * @return (string|null)[]
     */
    private function getHostAndPort(string $domain): array
    {
        if (false !== strpos($domain, ':')) {
            return explode(':', $domain, 2);
        }

        return [$domain, null];
    }

    /**
     * Returns the auto_item key from the parameters or the global array.
     *
     * @return string[]
     */
    private function getAutoItems(array $parameters): array
    {
        if (isset($parameters['auto_item'])) {
            return [$parameters['auto_item']];
        }

        if (isset($GLOBALS['TL_AUTO_ITEM']) && \is_array($GLOBALS['TL_AUTO_ITEM'])) {
            return $GLOBALS['TL_AUTO_ITEM'];
        }

        return [];
    }
}
