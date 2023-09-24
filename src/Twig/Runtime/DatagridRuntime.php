<?php

namespace HBM\DatagridBundle\Twig\Runtime;

use HBM\DatagridBundle\Service\QueryEncoder;
use Twig\Extension\RuntimeExtensionInterface;

class DatagridRuntime implements RuntimeExtensionInterface
{
    private QueryEncoder $queryEncoder;

    public function __construct(QueryEncoder $queryEncoder)
    {
        $this->queryEncoder = $queryEncoder;
    }

    public function hbmDatagridSearchEncode($var, string $mode = null)
    {
        return $this->queryEncoder->getQueryString($var, $mode);
    }

    public function hbmDatagridSearchDecode($var, string $mode = null)
    {
        return $this->queryEncoder->getQueryParams($var, $mode);
    }
}
