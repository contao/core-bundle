<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Cors;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use Nelmio\CorsBundle\Options\ProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the configuration for the nelmio/cors-bundle.
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
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(Request $request)
    {
        if (!$this->isCorsRequest($request) || !$this->canRunDbQuery()) {
            return [];
        }

        $stmt = $this->connection->prepare("
            SELECT EXISTS (
                SELECT
                    id
                FROM
                    tl_page
                WHERE
                    type = 'root' AND dns = :dns
            )
        ");

        $stmt->bindValue('dns', preg_replace('@^https?://@', '', $request->headers->get('origin')));
        $stmt->execute();

        if (!$stmt->fetchColumn()) {
            return [];
        }

        return [
            'allow_origin' => true,
            'allow_methods' => ['HEAD', 'GET'],
            'allow_headers' => ['x-requested-with'],
        ];
    }

    /**
     * Checks if the request has an Origin header.
     *
     * @param Request $request
     *
     * @return bool
     */
    private function isCorsRequest(Request $request)
    {
        return $request->headers->has('Origin')
            && $request->headers->get('Origin') !== $request->getSchemeAndHttpHost()
        ;
    }

    /**
     * Checks if the tl_page table exists.
     *
     * @return bool
     */
    private function canRunDbQuery()
    {
        try {
            return $this->connection->getSchemaManager()->tablesExist(['tl_page']);
        } catch (DriverException $e) {
            return false;
        }
    }
}
