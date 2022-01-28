<?php

namespace HBM\DatagridBundle\Twig;

use HBM\DatagridBundle\Service\QueryEncoder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DatagridExtension extends AbstractExtension {

  private QueryEncoder $queryEncoder;

  public function __construct(QueryEncoder $queryEncoder) {
    $this->queryEncoder = $queryEncoder;
  }

  /****************************************************************************/
  /* DEFINITIONS                                                              */
  /****************************************************************************/

  public function getFilters() : array {
    return [
      new TwigFilter('hbmDatagridSearchEncode', [$this, 'hbmDatagridSearchEncode']),
      new TwigFilter('hbmDatagridSearchDecode', [$this, 'hbmDatagridSearchDecode']),
    ];
  }

  /****************************************************************************/
  /* FILTER                                                                   */
  /****************************************************************************/

  public function hbmDatagridSearchEncode($var, string $mode = null) {
    return $this->queryEncoder->getQueryString($var, $mode);
  }

  public function hbmDatagridSearchDecode($var, string $mode = null) {
    return $this->queryEncoder->getQueryParams($var, $mode);
  }

}
