<?php

namespace HBM\DatagridBundle\Service\QueryBuilderStrategy\Common;

use HBM\DatagridBundle\Model\Datagrid;

abstract class AbstractQueryBuilderStrategy implements QueryBuilderStrategyInterface {

  /**
   * @var string
   */
  protected $distinctFieldName;

  /**
   * @var Datagrid
   */
  protected $datagrid;

  /**
   * Set distinctFieldName.
   *
   * @param string $distinctFieldName
   *
   * @return self
   */
  public function setDistinctFieldName(string $distinctFieldName) : self {
    $this->distinctFieldName = $distinctFieldName;

    return $this;
  }

  /**
   * Get distinctFieldName.
   *
   * @return string
   */
  public function getDistinctFieldName() : string {
    return $this->distinctFieldName;
  }

  /**
   * Set datagrid.
   *
   * @param Datagrid $datagrid
   *
   * @return self
   */
  public function setDatagrid(Datagrid $datagrid) : self {
    $this->datagrid = $datagrid;

    return $this;
  }

  /**
   * Get datagrid.
   *
   * @return Datagrid
   */
  public function getDatagrid() : Datagrid {
    return $this->datagrid;
  }

}
