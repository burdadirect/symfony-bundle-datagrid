<?php

namespace HBM\DatagridBundle\Service\QueryBuilderStrategy\Common;

use HBM\DatagridBundle\Model\Export;

interface QueryBuilderStrategyInterface {

  public function count() : int;

  public function doExport(Export $export) : Export;

  public function getResults() : array;

}
