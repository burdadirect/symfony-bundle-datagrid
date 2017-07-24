<?php

namespace HBM\DatagridBundle\Model;

use Symfony\Component\HttpFoundation\Response;

class ExportJSON extends Export {

  /** @var array */
  protected $lines;

  /** @var array */
  protected $labels;

  public function init() {
  }

  public function addHeader() {
    $this->labels = [];

    /** @var TableCell $cell */
    foreach ($this->getCells() as $index => $cell) {
      if ($cell->isVisibleExport()) {
        $this->labels[$index] = $this->prepareLabel($cell->getLabel());
      }
    }
  }

  public function addRow($obj) {
    $line = [];

    $row = count($this->lines);

    /** @var TableCell $cell */
    $column = 0;
    foreach ($this->getCells() as $index => $cell) {
      if ($cell->isVisibleExport()) {
        $line[$this->labels[$index]] = $this->prepareValue($cell->parseValue($obj, $column, $row));
        $column++;
      }
    }

    $this->lines[] = $line;
  }

  private function prepareLabel($label) {
    return html_entity_decode(strip_tags($label));
  }

  private function prepareValue($value) {
    if (is_array($value)) {
      return $value;
    } else {
      return strip_tags($value);
    }
  }

  public function output() {
    $content = json_encode($this->lines);

    return new Response($content, 200, [
      'Pragma' => 'no-cache',
      'Cache-Control' => 'Cache-Control: must-revalidate, post-check=0, pre-check=0',
      'Last-Modified' => gmdate('D, d M Y H:i:s').' GMT',
      'Content-Type' => 'text/json',
      'Content-Disposition' => 'attachment; filename="'.$this->name.'.json"',
      'Content-Length' => strlen($content),
      'Accept-Ranges' => 'bytes',
    ]);
  }

}
