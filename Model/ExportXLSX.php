<?php

namespace HBM\DatagridBundle\Model;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportXLSX extends Export {

  /** @var \PHPExcel */
  protected $excel;

  /** @var \PHPExcel_Worksheet */
  protected $sheet;

  /** @var int */
  protected $row = 1;

  public function init() {
    $this->excel = new \PHPExcel();
    $this->excel->getProperties()->setTitle('Datagrid-Export');

    $this->sheet = $this->excel->setActiveSheetIndex(0);
    $this->sheet->setTitle('Export');
  }

  function addHeader() {
    /** @var TableCell $cell */
    $column = 0;
    foreach ($this->getCells() as $cell) {
      if ($cell->isVisibleExport()) {
        $this->sheet->setCellValueByColumnAndRow($column, $this->row, $cell->getLabel());
        $column++;
      }
    }

    $this->row++;
  }

  public function addRow($obj) {
    /** @var TableCell $cell */
    $column = 0;
    foreach ($this->getCells() as $cell) {
      if ($cell->isVisibleExport()) {
        $this->sheet->setCellValueByColumnAndRow($column, $this->row, $this->prepareValue($cell->parseValue($obj, $column, $this->row - 2)));
        $column++;
      }
    }

    $this->row++;
  }

  private function prepareValue($value) {
    if (is_array($value)) {
      return implode("\n", $value);
    } else {
      return strip_tags($value);
    }
  }

  public function output() {
    $excelWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');

    $callable = function() use ($excelWriter) {
      $excelWriter->save('php://output');
    };

    return new StreamedResponse($callable, 200, [
      'Pragma' => 'no-cache',
      'Cache-Control' => 'Cache-Control: must-revalidate, post-check=0, pre-check=0',
      'Last-Modified' => gmdate("D, d M Y H:i:s").' GMT',
      'Content-Type' => 'application/vnd.ms-excel',
      'Content-Disposition' => 'attachment; filename="'.$this->name.'.xlsx"',
      'Accept-Ranges' => 'bytes',
    ]);
  }

}
