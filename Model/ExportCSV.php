<?php

namespace HBM\DatagridBundle\Model;

use Symfony\Component\HttpFoundation\Response;

class ExportCSV extends Export {

  protected array $lines = [];

  public function addHeader() : void {
    $line = [];

    /** @var TableCell $cell */
    foreach ($this->getCells() as $cell) {
      if ($cell->isVisibleExport()) {
        $line[] = $this->encloseValue($this->prepareLabel($cell->getLabelText()));
      }
    }

    $this->lines[] = implode(';', $line);
  }

  /**
   * @param $obj
   *
   * @throws \InvalidArgumentException
   */
  public function addRow($obj) : void {
    $line = [];

    $row = \count($this->lines) - 1;

    /** @var TableCell $cell */
    $column = 0;
    foreach ($this->getCells() as $cell) {
      if ($cell->isVisibleExport()) {
        $value = $cell->setFormatter($this)->getValue($obj, $column, $row);;

        $line[] = $this->encloseValue($value);

        $column++;
      }
    }

    $this->lines[] = implode(';', $line);
  }

  /**
   * @param $value
   *
   * @return string
   */
  private function encloseValue($value) : string {
    return '"'.str_replace('"', '""', $value).'"';
  }

  /**
   * @return Response
   *
   * @throws \InvalidArgumentException
   */
  public function output() : Response {
    $content = utf8_decode(implode("\n", $this->lines));

    return new Response($content, 200, [
      'Pragma' => 'no-cache',
      'Cache-Control' => 'Cache-Control: must-revalidate, post-check=0, pre-check=0',
      'Last-Modified' => gmdate('D, d M Y H:i:s').' GMT',
      'Content-Type' => 'text/csv',
      'Content-Disposition' => 'attachment; filename="'.$this->name.'.csv"',
      'Content-Length' => \strlen($content),
      'Accept-Ranges' => 'bytes',
    ]);
  }

}
