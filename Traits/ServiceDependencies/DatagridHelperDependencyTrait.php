<?php

namespace HBM\DatagridBundle\Traits\ServiceDependencies;

use HBM\DatagridBundle\Service\DatagridHelper;

trait DatagridHelperDependencyTrait {

  protected DatagridHelper $datagridHelper;

  /**
   * @required
   *
   * @param DatagridHelper $datagridHelper
   *
   * @return void
   */
  public function setDatagridHelper(DatagridHelper $datagridHelper): void {
    $this->datagridHelper = $datagridHelper;
  }

}
