<?php

namespace HBM\DatagridBundle\Model;

use Symfony\Component\HttpFoundation\Response;

class ExportJSON extends Export {

  protected array $lines = [];

  protected array $labels = [];

  public function addHeader() : void {
    $this->labels = [];

    /** @var TableCell $cell */
    foreach ($this->getCells() as $index => $cell) {
      if ($cell->isVisibleExport()) {
        $this->labels[$index] = $this->prepareLabel($cell->getLabelText());
      }
    }
  }

  /**
   * @param $obj
   *
   * @throws \InvalidArgumentException
   */
  public function addRow($obj) : void {
    $line = [];

    $row = \count($this->lines);

    /** @var TableCell $cell */
    $column = 0;
    foreach ($this->getCells() as $index => $cell) {
      if ($cell->isVisibleExport()) {
        $value = $cell->setFormatter($this)->getValue($obj, $column, $row);

        $line[$this->labels[$index]] = $value;

        $column++;
      }
    }

    $this->lines[] = $line;
  }

  /**
   * @param TableCell $cell
   * @param array $value
   * @param string|null $separator
   *
   * @return array
   */
  protected function formatCellValueArray(TableCell $cell, array $value, ?string $separator = ',') : array {
    return $value;
  }

  /**
   * @return Response
   *
   * @throws \InvalidArgumentException
   */
  public function output() : Response {
    $content = json_encode($this->lines);

    return new Response($content, 200, [
      'Pragma' => 'no-cache',
      'Cache-Control' => 'Cache-Control: must-revalidate, post-check=0, pre-check=0',
      'Last-Modified' => gmdate('D, d M Y H:i:s').' GMT',
      'Content-Type' => 'text/json',
      'Content-Disposition' => 'attachment; filename="'.$this->name.'.json"',
      'Content-Length' => \strlen($content),
      'Accept-Ranges' => 'bytes',
    ]);
  }

}
