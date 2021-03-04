<?php

namespace HBM\DatagridBundle\Model;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Drawing as SharedDrawing;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportXLSX extends Export {

  /** @var Spreadsheet */
  protected $spreadsheet;

  /** @var Worksheet */
  protected $sheet;

  /** @var int */
  protected $row = 1;

  /** @var array */
  protected $columnsWidths = [];

  /**
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public function init() : void {
    $this->spreadsheet = new Spreadsheet();
    $this->spreadsheet->getProperties()->setTitle('Datagrid-Export');

    $this->sheet = $this->spreadsheet->setActiveSheetIndex(0);
    $this->sheet->setTitle('Export');
  }

  public function finish() : void {
    foreach ($this->columnsWidths as $columnName => $columnWidth) {
      $columnWidthCalculated = $columnWidth;
      if (substr($columnWidthCalculated, -2) === 'px') {
        $columnWidthCalculated = SharedDrawing::pixelsToCellDimension((int)substr($columnWidthCalculated, 0, -2), $this->spreadsheet->getDefaultStyle()->getFont());
      }
      $this->sheet->getColumnDimension($columnName)->setWidth($columnWidthCalculated);
    }
  }

  public function addHeader() : void {
    /** @var TableCell $cell */
    $column = 1;
    foreach ($this->getCells() as $cell) {
      if ($cell->isVisibleExport()) {
        $this->sheet->setCellValueByColumnAndRow($column, $this->row, $this->prepareLabel($cell->getLabelText()));
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
    $column = 1;
    $columnWidth = 0;

    /** @var TableCell $cell */
    foreach ($this->getCells() as $cell) {
      if ($cell->isVisibleExport()) {
        $value = $cell->parseValue($obj, $column, $this->row - 2);
        if ($value instanceof \SplFileInfo) {
          if (!$this->setCellImageByColumnAndRow($cell, $column, $this->row, $value, $columnWidth)) {
            $this->sheet->setCellValueByColumnAndRow($column, $this->row, $this->prepareValue($value->getBasename()));
          }
        } else {
          $this->sheet->setCellValueByColumnAndRow($column, $this->row, $this->prepareValue($value));
        }

        $column++;
      }
    }

    $this->row++;
  }

  private function setCellImageByColumnAndRow(TableCell $cell, $column, $row, \SplFileInfo $file, &$columnWidth) : bool {
    $imageInfo = getimagesize($file->getPathname());
    if ($imageInfo === FALSE) {
      return FALSE;
    }

    $columnName = Coordinate::stringFromColumnIndex($column);
    $columnWidth = max($columnWidth, $imageInfo[0]);
    $columnOffset = 10;

    $this->columnsWidths[$columnName] = ($columnWidth + 2*$columnOffset).'px';

    $drawing = new Drawing();
    $drawing->setName($cell->getLabelText());
    $drawing->setDescription($file->getBasename());
    $drawing->setPath($file->getPathname());
    $drawing->setOffsetX($columnOffset);
    $drawing->setOffsetY($columnOffset);
    $drawing->setCoordinates($columnName.$row);
    $drawing->setWidth($imageInfo[0]);
    $drawing->setHeight($imageInfo[1]);

    $drawing->setWorksheet($this->sheet);

    $this->sheet->getRowDimension($row)->setRowHeight(SharedDrawing::pixelsToPoints($imageInfo[1] + 2*$columnOffset));

    return TRUE;
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
