<?php

namespace HBM\DatagridBundle\Traits\ServiceDependencies;

use HBM\DatagridBundle\Service\DatagridHelper;
use Symfony\Contracts\Service\Attribute\Required;

trait DatagridHelperDependencyTrait
{
    protected DatagridHelper $datagridHelper;

    #[Required]
    public function setDatagridHelper(DatagridHelper $datagridHelper): void
    {
        $this->datagridHelper = $datagridHelper;
    }
}
