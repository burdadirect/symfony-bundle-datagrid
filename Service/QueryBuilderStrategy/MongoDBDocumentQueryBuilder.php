<?php

namespace HBM\DatagridBundle\Service\QueryBuilderStrategy;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Query\Builder;
use HBM\DatagridBundle\Model\Export;
use HBM\DatagridBundle\Service\QueryBuilderStrategy\Common\AbstractQueryBuilderStrategy;

class MongoDBDocumentQueryBuilder extends AbstractQueryBuilderStrategy {

  /**
   * @var Builder
   */
  private $qb;

  /**
   * @var DocumentManager
   */
  private $dm;

  /**
   * Set queryBuilder.
   *
   * @param Builder|null $queryBuilder
   *
   * @return self
   */
  public function setQueryBuilder(?Builder $queryBuilder) : self {
    $this->qb = $queryBuilder;

    return $this;
  }

  /**
   * Get queryBuilder.
   *
   * @return Builder|null
   */
  public function getQueryBuilder() : ?Builder {
    return $this->qb;
  }

  /**
   * Set documentManager.
   *
   * @param DocumentManager $documentManager
   *
   * @return self
   */
  public function setDocumentManager(DocumentManager $documentManager) : self {
    $this->dm = $documentManager;

    return $this;
  }

  /**
   * Get documentManager.
   *
   * @return DocumentManager
   */
  public function getDocumentManager() : DocumentManager {
    return $this->dm;
  }

  /****************************************************************************/
  /* INTERFACE                                                                */
  /****************************************************************************/

  /**
   * @return int
   */
  public function count(): int {
    if (!$this->getQueryBuilder()) {
      return 0;
    }

    $qbNum = clone $this->qb;
    $qbNum->distinct($this->getDistinctFieldName());

    return $qbNum->count()->getQuery()->execute();
  }

  /**
   * @return array
   *
   * @throws MongoDBException
   */
  public function getResults(): array {
    if (!$this->getQueryBuilder()) {
      return [];
    }

    $qbRes = clone $this->qb;
    $qbRes->skip($this->getDatagrid()->getPagination()->getOffset());
    $qbRes->limit($this->getDatagrid()->getMaxEntriesPerPage());

    return $qbRes->getQuery()->execute()->toArray();
  }

  /**
   * @param Export $export
   *
   * @return Export
   *
   * @throws MongoDBException
   */
  public function doExport(Export $export) : Export {
    if (!$this->getQueryBuilder()) {
      return $export;
    }

    $offset = 0;
    $batchSize = 100;

    $exporting = TRUE;
    while ($exporting) {
      $exporting = FALSE;

      $qbExport = clone $this->qb;
      $qbExport->skip($offset);
      $qbExport->limit($batchSize);

      $documents = $qbExport->getQuery()->getIterator();
      foreach ($documents as $document) {
        $exporting = TRUE;
        $export->addRow($document);
        $offset++;
      }

      $this->getDocumentManager()->clear();
    }

    return $export;
  }

}
