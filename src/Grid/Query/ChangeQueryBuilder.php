<?php
/**
 * Copyright since 2019 Kaudaj
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@kaudaj.com so we can send you a copy immediately.
 *
 * @author    Kaudaj <info@kaudaj.com>
 * @copyright Since 2019 Kaudaj
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace Kaudaj\Module\DBVCS\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Kaudaj\Module\DBVCS\Repository\ChangeLangRepository;
use Kaudaj\Module\DBVCS\Repository\ChangeRepository;
use PrestaShop\PrestaShop\Adapter\Shop\Context as ShopContext;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class ChangeQueryBuilder extends AbstractDoctrineQueryBuilder
{
    /**
     * @var int
     */
    private $contextLangId;

    /**
     * @var ShopContext
     */
    private $shopContext;

    public function __construct(Connection $connection, string $dbPrefix, int $contextLangId, ShopContext $shopContext)
    {
        parent::__construct($connection, $dbPrefix);

        $this->contextLangId = $contextLangId;
        $this->shopContext = $shopContext;
    }

    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $qb = $this->getBaseQuery();
        $qb->select('c.id_change, cl.description, c.commit, c.date_add');

        if ($searchCriteria->getOffset() !== null) {
            $qb->setFirstResult($searchCriteria->getOffset());
        }

        if ($searchCriteria->getLimit() !== null) {
            $qb->setMaxResults($searchCriteria->getLimit());
        }

        if ($searchCriteria->getOrderBy() !== null && $searchCriteria->getOrderWay() !== null) {
            $qb->orderBy(
                $searchCriteria->getOrderBy(),
                $searchCriteria->getOrderWay()
            );
        }

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ('id_change' === $filterName) {
                $qb->andWhere("c.id_change = :$filterName");
                $qb->setParameter($filterName, $filterValue);

                continue;
            }

            $qb->andWhere("$filterName LIKE :$filterName");
            $qb->setParameter($filterName, '%' . $filterValue . '%');
        }

        return $qb;
    }

    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $qb = $this->getBaseQuery();
        $qb->select('COUNT(c.id_change)');

        return $qb;
    }

    private function getBaseQuery(): QueryBuilder
    {
        $shopConstraint = $this->shopContext->getShopConstraint();
        $shopId = $shopConstraint->getShopId() !== null ? $shopConstraint->getShopId()->getValue() : null;
        $shopGroupId = $shopConstraint->getShopGroupId() !== null ? $shopConstraint->getShopGroupId()->getValue() : null;

        $query = $this->connection
            ->createQueryBuilder()
            ->from(ChangeRepository::TABLE_NAME, 'c')
            ->leftJoin('c', ChangeLangRepository::TABLE_NAME, 'cl', 'c.id_change = cl.id_change AND id_lang = :langId')
            ->setParameter('langId', $this->contextLangId)
        ;

        if ($shopId !== null) {
            $query
                ->andWhere('c.id_shop = :shopId')
                ->setParameter('shopId', $shopId)
            ;
        }

        if ($shopGroupId !== null) {
            $query
                ->andWhere('c.id_shop_group = :shopGroupId')
                ->setParameter('shopGroupId', $shopGroupId)
            ;
        }

        return $query;
    }
}
