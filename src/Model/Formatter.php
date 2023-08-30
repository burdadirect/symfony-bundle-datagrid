<?php

namespace HBM\DatagridBundle\Model;

class Formatter
{
    public function formatCellValue(TableCell $cell, $value)
    {
        if ($value instanceof \DateTime) {
            return $this->formatCellValueDateTime($cell, $value);
        }

        if (is_array($value)) {
            return $this->formatCellValueArray($cell, $value);
        }

        if ($value instanceof \SplFileInfo) {
            return $this->formatCellValueSplFileInfo($cell, $value);
        }

        return $this->formatCellValueString($cell, $value);
    }

    public function formatCellValueString(TableCell $cell, $value)
    {
        return $value;
    }

    protected function formatCellValueArray(TableCell $cell, array $value, ?string $separator = ', ')
    {
        return implode($cell->getOption('separator', $separator), $value);
    }

    protected function formatCellValueDateTime(TableCell $cell, \DateTime $value, ?string $format = 'Y-m-d H:i:s')
    {
        return $value->format($cell->getOption('format', $format));
    }

    protected function formatCellValueSplFileInfo(TableCell $cell, \SplFileInfo $value)
    {
        return $value->getBasename();
    }
}
