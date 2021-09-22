<?php

namespace HBM\DatagridBundle\Model;

class Formatter {

  /**
   * @param TableCell $cell
   * @param mixed $value
   *
   * @return mixed
   */
  public function formatCellValue(TableCell $cell, $value) {
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

  /**
   * @param TableCell $cell
   * @param $value
   *
   * @return mixed
   */
  public function formatCellValueString(TableCell $cell, $value) {
    return $value;
  }

  /**
   * @param TableCell $cell
   * @param array $value
   * @param string|null $separator
   *
   * @return mixed
   */
  protected function formatCellValueArray(TableCell $cell, array $value, ?string $separator = ', ') {
    return implode($cell->getOption('separator', $separator), $value);
  }

  /**
   * @param TableCell $cell
   * @param \DateTime $value
   * @param string|null $format
   *
   * @return mixed
   */
  protected function formatCellValueDateTime(TableCell $cell, \DateTime $value, ?string $format = 'Y-m-d H:i:s') {
    return $value->format($cell->getOption('format', $format));
  }

  /**
   * @param TableCell $cell
   * @param \SplFileInfo $value
   *
   * @return mixed
   */
  protected function formatCellValueSplFileInfo(TableCell $cell, \SplFileInfo $value) {
    return $value->getBasename();
  }

}
