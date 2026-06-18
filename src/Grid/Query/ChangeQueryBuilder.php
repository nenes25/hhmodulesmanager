<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file docs/licenses/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@h-hennes.fr so we can send you a copy immediately.
 *
 * @author    Hervé HENNES <contact@h-hhennes.fr>
 * @copyright since 2023 Hervé HENNES
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License ("AFL") v. 3.0
 */

namespace Hhennes\ModulesManager\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

class ChangeQueryBuilder extends AbstractDoctrineQueryBuilder
{
    private DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator;

    public function __construct(
        Connection $connection,
        string $dbPrefix,
        DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator,
    ) {
        parent::__construct($connection, $dbPrefix);
        $this->searchCriteriaApplicator = $searchCriteriaApplicator;
    }

    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQuery($searchCriteria->getFilters());
        $qb->select(
            'c.id_change',
            'c.entity',
            'c.action',
            'c.`key`',
            'c.details',
            'c.date_add',
            'c.date_upd'
        );

        $this->searchCriteriaApplicator
            ->applySorting($searchCriteria, $qb)
            ->applyPagination($searchCriteria, $qb);

        return $qb;
    }

    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQuery($searchCriteria->getFilters());
        $qb->select('COUNT(c.id_change)');

        return $qb;
    }

    private function getBaseQuery(array $filters): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->from($this->dbPrefix . 'hhmodulesmanager_change', 'c');

        foreach ($filters as $name => $value) {
            if ('id_change' === $name) {
                $qb->andWhere('c.id_change = :id_change')
                    ->setParameter('id_change', (int) $value);
            } elseif ('entity' === $name) {
                $qb->andWhere('c.entity LIKE :entity')
                    ->setParameter('entity', '%' . $value . '%');
            } elseif ('action' === $name) {
                $qb->andWhere('c.action LIKE :action')
                    ->setParameter('action', '%' . $value . '%');
            } elseif ('key' === $name) {
                $qb->andWhere('c.`key` LIKE :key')
                    ->setParameter('key', '%' . $value . '%');
            }
        }

        return $qb;
    }
}
