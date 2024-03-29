<?php

namespace HBM\DatagridBundle\Service\QueryBuilderStrategy;

use Doctrine\ORM\QueryBuilder;
use HBM\DatagridBundle\Model\Export;
use HBM\DatagridBundle\Service\QueryBuilderStrategy\Common\AbstractQueryBuilderStrategy;

class EntityQueryBuilder extends AbstractQueryBuilderStrategy
{
    private ?QueryBuilder $qb = null;

    /**
     * Set queryBuilder.
     */
    public function setQueryBuilder(?QueryBuilder $queryBuilder): self
    {
        $this->qb = $queryBuilder;

        return $this;
    }

    /**
     * Get queryBuilder.
     */
    public function getQueryBuilder(): ?QueryBuilder
    {
        return $this->qb;
    }

    /* INTERFACE */

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function count(): int
    {
        if (!$this->getQueryBuilder()) {
            return 0;
        }

        $qbNum       = clone $this->qb;
        $rootAliases = $qbNum->getRootAliases();
        $rootAlias   = reset($rootAliases);
        $qbNum->select($qbNum->expr()->countDistinct($rootAlias . '.' . $this->getDistinctFieldName()));
        $qbNum->resetDQLPart('orderBy');

        $query = $qbNum->getQuery();

        if ($this->getDatagrid()->getCacheEnabled()) {
            $query->enableResultCache(
                $this->getDatagrid()->getCacheSeconds(),
                $this->getDatagrid()->getCachePrefix() . '_scalar'
            );
        }

        return $query->getSingleScalarResult();
    }

    public function getResults(): array
    {
        if (!$this->getQueryBuilder()) {
            return [];
        }

        $qbRes = clone $this->qb;
        $qbRes->setFirstResult($this->getDatagrid()->getPagination()->getOffset());
        $qbRes->setMaxResults($this->getDatagrid()->getMaxEntriesPerPage());

        $query = $qbRes->getQuery();

        if ($this->getDatagrid()->getCacheEnabled()) {
            $query->enableResultCache(
                $this->getDatagrid()->getCacheSeconds(),
                $this->getDatagrid()->getCachePrefix() . '_scalar'
            );
        }

        return $query->getResult();
    }

    public function doExport(Export $export): Export
    {
        if (!$this->getQueryBuilder()) {
            return $export;
        }

        $offset    = 0;
        $batchSize = 100;

        $exporting = true;
        while ($exporting) {
            $exporting = false;

            $qbExport = clone $this->qb;
            $qbExport->setFirstResult($offset);
            $qbExport->setMaxResults($batchSize);

            $entities = $qbExport->getQuery()->toIterable();
            foreach ($entities as $entity) {
                $exporting = true;
                $export->addRow($entity);
                ++$offset;
            }

            $this->getQueryBuilder()->getEntityManager()->clear();
        }

        return $export;
    }
}
