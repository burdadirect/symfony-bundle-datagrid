<?php

namespace HBM\DatagridBundle\Service\QueryBuilderStrategy;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Query\Builder;
use HBM\DatagridBundle\Model\Export;
use HBM\DatagridBundle\Service\QueryBuilderStrategy\Common\AbstractQueryBuilderStrategy;

class MongoDBDocumentQueryBuilder extends AbstractQueryBuilderStrategy
{
    private ?Builder $qb = null;
    private ?DocumentManager $dm = null;

    /**
     * Set queryBuilder.
     */
    public function setQueryBuilder(?Builder $queryBuilder): self
    {
        $this->qb = $queryBuilder;

        return $this;
    }

    /**
     * Get queryBuilder.
     */
    public function getQueryBuilder(): ?Builder
    {
        return $this->qb;
    }

    /**
     * Set documentManager.
     */
    public function setDocumentManager(DocumentManager $documentManager): self
    {
        $this->dm = $documentManager;

        return $this;
    }

    /**
     * Get documentManager.
     */
    public function getDocumentManager(): DocumentManager
    {
        return $this->dm;
    }

    /* INTERFACE */

    /**
     * @throws MongoDBException
     */
    public function count(): int
    {
        if (!$this->getQueryBuilder()) {
            return 0;
        }

        $qbNum = clone $this->qb;
        $qbNum->distinct($this->getDistinctFieldName());

        return $qbNum->count()->getQuery()->execute();
    }

    /**
     * @throws MongoDBException
     */
    public function getResults(): array
    {
        if (!$this->getQueryBuilder()) {
            return [];
        }

        $qbRes = clone $this->qb;
        $qbRes->skip($this->getDatagrid()->getPagination()->getOffset());
        $qbRes->limit($this->getDatagrid()->getMaxEntriesPerPage());

        return $qbRes->getQuery()->execute()->toArray();
    }

    /**
     * @throws MongoDBException
     */
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
            $qbExport->skip($offset);
            $qbExport->limit($batchSize);

            $documents = $qbExport->getQuery()->getIterator();
            foreach ($documents as $document) {
                $exporting = true;
                $export->addRow($document);
                ++$offset;
            }

            $this->getDocumentManager()->clear();
        }

        return $export;
    }
}
