<?php

namespace HBM\DatagridBundle\Model;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportXLSX extends Export {

  /** @var Spreadsheet */
  protected $spreadsheet;

  /** @var \PHPExcel_Worksheet */
  protected $sheet;

  /** @var int */
  protected $row = 1;

  /**
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public function init() : void {
    $this->spreadsheet = new Spreadsheet();
    $this->spreadsheet->getProperties()->setTitle('Datagrid-Export');

    $this->sheet = $this->spreadsheet->setActiveSheetIndex(0);
    $this->sheet->setTitle('Export');
  }

  public function addHeader() : void {
    /** @var TableCell $cell */
    $column = 0;
    foreach ($this->getCells() as $cell) {
      if ($cell->isVisibleExport()) {
        $this->sheet->setCellValueByColumnAndRow($column, $this->row, $this->prepareLabel($cell->getLabel()));
        $column++;
      }
    }

    $this->row++;
  }

  /**
   * @param $obj
   *
   * @throws \InvalidArgumentException
   */
  public function addRow($obj) : void {
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

  private function prepareLabel($label) {
    return html_entity_decode(strip_tags($label));
  }

  private function prepareValue($value) {
    if (\is_array($value)) {
      return implode("\n", $value);
    }

    return strip_tags($value);
  }

  /**
   * @return StreamedResponse
   */
  public function output() : StreamedResponse {
    $writer = new Xlsx($this->spreadsheet);

    $callable = function() use ($writer) {
      $writer->save('php://output');
    };

    return new StreamedResponse($callable, 200, [
      'Pragma' => 'no-cache',
      'Cache-Control' => 'Cache-Control: must-revalidate, post-check=0, pre-check=0',
      'Last-Modified' => gmdate('D, d M Y H:i:s').' GMT',
      'Content-Type' => 'application/vnd.ms-excel',
      'Content-Disposition' => 'attachment; filename="'.$this->name.'.xlsx"',
      'Accept-Ranges' => 'bytes',
    ]);
  }

}
