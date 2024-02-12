<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\EventListener\DataContainer\Undo;

use Contao\Backend;
use Contao\Controller;
use Contao\CoreBundle\DataContainer\DataContainerOperation;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
#[AsCallback(table: 'tl_undo', target: 'list.operations.jumpToParent.button')]
class JumpToParentOperationListener
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
    ) {
    }

    public function __invoke(DataContainerOperation $operation): void
    {
        $row = $operation->getRecord();
        $table = $row['fromTable'];
        $originalRow = StringUtil::deserialize($row['data'])[$table][0];
        $parent = $this->getParentTableForRow($table, $originalRow);

        if (
            !$parent
            || !$this->checkIfParentExists($parent)
            || !$this->security->isGranted(ContaoCorePermissions::DC_PREFIX.$table, new CreateAction($table, $originalRow))
        ) {
            $operation->disable();

            return;
        }

        $parentLinkParameters = $this->getParentLinkParameters($parent, $table);

        if (!$parentLinkParameters) {
            $operation->disable();

            return;
        }

        $newTitle = sprintf(
            $this->translator->trans('tl_undo.parent_modal', [], 'contao_tl_undo'),
            $table,
            $originalRow['id'],
        );

        $backend = $this->framework->getAdapter(Backend::class);

        $operation->setUrl($backend->addToUrl($parentLinkParameters.'&popup=1'));
        $operation['title'] = $newTitle;
        $operation['attributes'] = ' onclick="Backend.openModalIframe({\'title\':\''.StringUtil::specialchars($newTitle).'\',\'url\': this.href });return false"';
    }

    private function getParentLinkParameters(array $parent, string $table): string
    {
        if (!$parent) {
            return '';
        }

        $controller = $this->framework->getAdapter(Controller::class);
        $controller->loadDataContainer($parent['table']);

        $module = $this->getModuleForTable($parent['table']);

        if (!$module) {
            return '';
        }

        $params = ['do' => $module['_module_name']];

        if (DataContainer::MODE_TREE === $GLOBALS['TL_DCA'][$parent['table']]['list']['sorting']['mode']) {
            // Limit tree to right parent node
            $params['pn'] = $parent['id'];
        } elseif ($module['tables'][0] !== $table) {
            // If $table is the main table of a module, we just go to do=$module, else we
            // append the right table and ID
            $params['table'] = $table;
            $params['id'] = $parent['id'];
        }

        return http_build_query($params, '', '&amp;', PHP_QUERY_RFC3986);
    }

    private function getModuleForTable(string $table): array|null
    {
        foreach ($GLOBALS['BE_MOD'] as $group) {
            foreach ($group as $name => $config) {
                if (\is_array($config['tables'] ?? null) && \in_array($table, $config['tables'], true)) {
                    return [...$config, '_module_name' => $name];
                }
            }
        }

        return null;
    }

    private function getParentTableForRow(string $table, array $row): array|null
    {
        if (true === ($GLOBALS['TL_DCA'][$table]['config']['dynamicPtable'] ?? null)) {
            return ['table' => $row['ptable'], 'id' => $row['pid']];
        }

        if (isset($GLOBALS['TL_DCA'][$table]['config']['ptable'])) {
            return ['table' => $GLOBALS['TL_DCA'][$table]['config']['ptable'], 'id' => $row['pid']];
        }

        return null;
    }

    private function checkIfParentExists(array $parent): bool
    {
        $count = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM '.$this->connection->quoteIdentifier($parent['table']).' WHERE id = :id',
            [
                'id' => $parent['id'],
            ],
        );

        return (int) $count > 0;
    }
}
