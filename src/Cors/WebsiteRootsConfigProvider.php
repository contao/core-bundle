<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Cors;

use Doctrine\DBAL\Connection;
use Nelmio\CorsBundle\Options\ProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the configuration for integration with the
 * nelmio/cors-bundle.
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class WebsiteRootsConfigProvider implements ProviderInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * WebsiteRootsProvider constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns CORS options for $request.
     *
     * Any valid CORS option will overwrite those of the previous ones.
     * The method must at least return an empty array.
     *
     * All keys of the bundle's semantical configuration are valid:
     * - bool allow_credentials
     * - bool allow_origin
     * - bool allow_headers
     * - bool origin_regex
     * - array allow_methods
     * - array expose_headers
     * - int max_age
     *
     * @param Request $request
     *
     * @return array CORS options
     */
    public function getOptions(Request $request)
    {
        $stmt = $this->connection->prepare('SELECT id FROM tl_page WHERE type=:type AND dns=:dns');
        $stmt->bindValue('type', 'root');
        $stmt->bindValue('dns', preg_replace('@^https?://@', '', $request->headers->get('origin')));
        $stmt->execute();

        if (0 === $stmt->rowCount()) {
            return [];
        }

        return [
            'allow_methods' => ['HEAD', 'GET'],
            'allow_headers' => ['x-requested-with'],
            'allow_origin'  => true,
        ];
    }
}
