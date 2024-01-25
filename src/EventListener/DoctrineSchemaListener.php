<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\EventListener;

use Contao\CoreBundle\Doctrine\Schema\DcaSchemaProvider;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;

/**
 * @internal
 */
#[AsDoctrineListener('postGenerateSchema')]
class DoctrineSchemaListener
{
    public function __construct(
        private readonly DcaSchemaProvider $provider,
        private readonly ContainerInterface $messengerTransportLocator,
    ) {
    }

    /**
     * Adds the Contao DCA information to the Doctrine schema.
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $event): void
    {
        $this->provider->appendToSchema($event->getSchema());

        foreach (['contao_prio_high', 'contao_prio_normal', 'contao_prio_low', 'contao_failure'] as $transportName) {
            if (!$this->messengerTransportLocator->has($transportName)) {
                continue;
            }

            $transport = $this->messengerTransportLocator->get($transportName);

            if (!$transport instanceof DoctrineTransport) {
                continue;
            }

            $transport->configureSchema($event->getSchema(), $event->getEntityManager()->getConnection());
        }
    }
}
