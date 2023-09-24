<?php

namespace HBM\DatagridBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HBMDatagridBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
