<?php

namespace HBM\DatagridBundle\Twig\Extension;

use HBM\DatagridBundle\Twig\Runtime\DatagridRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DatagridExtension extends AbstractExtension {

  /****************************************************************************/
  /* DEFINITIONS                                                              */
  /****************************************************************************/

  public function getFilters() : array {
    return [
      new TwigFilter('hbmDatagridSearchEncode', [DatagridRuntime::class, 'hbmDatagridSearchEncode']),
      new TwigFilter('hbmDatagridSearchDecode', [DatagridRuntime::class, 'hbmDatagridSearchDecode']),
    ];
  }

}
