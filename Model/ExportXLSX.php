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

  protected ?Spreadsheet $spreadsheet = null;

  protected ?Worksheet $sheet = null;

  protected int $row = 1;

  protected array $columnsWidths = [];

  /**
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public function init() : void {
    $this->spreadsheet = new Spreadsheet();
    $this->spreadsheet->getProperties()->setTitle('Datagrid-Export');

    $this->sheet = $this->spreadsheet->setActiveSheetIndex(0);
    $this->sheet->setTitle('Export');
  }

  /**
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
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
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public function addRow($obj) : void {
    $column = 1;
    $columnWidth = 0;

    /** @var TableCell $cell */
    foreach ($this->getCells() as $cell) {
      if ($cell->isVisibleExport()) {
        $value = $cell->setFormatter($this)->getValue($obj, $column, $this->row - 2);

        if ($value instanceof \SplFileInfo) {
          if (!$this->setCellImageByColumnAndRow($cell, $column, $this->row, $value, $columnWidth)) {
            $value = $value->getBasename();
          } else {
            $value = null;
          }
        }

        $this->sheet->setCellValueByColumnAndRow($column, $this->row, $value);

        $column++;
      }
    }

    $this->row++;
  }

  protected function formatCellValueArray(TableCell $cell, array $value, ?string $separator = "\n") {
    return parent::formatCellValueArray($cell, $value, $separator);
  }

  protected function formatCellValueSplFileInfo(TableCell $cell, \SplFileInfo $value) {
    return $value;
  }

  /**
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
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

  /**
   * @return StreamedResponse
   */
  public function response(): StreamedResponse {
    $writer = new Xlsx($this->spreadsheet);

    $callable = function() use ($writer) {
      $writer->save('php://output');
    };

    return new StreamedResponse($callable, 200, [
      'Pragma' => 'no-cache',
      'Cache-Control' => 'Cache-Control: must-revalidate, post-check=0, pre-check=0',
      'Last-Modified' => gmdate('D, d M Y H:i:s').' GMT',
      'Content-Type' => 'application/vnd.ms-excel',
      'Content-Disposition' => 'attachment; filename="'.$this->getName().'.xlsx"',
      'Accept-Ranges' => 'bytes',
    ]);
  }

  /**
   * @return resource|string|null
   *
   * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
   */
  public function stream() {
    $resource = fopen('php://temp', 'wb+');

    $writer = new Xlsx($this->spreadsheet);
    $writer->save($resource);

    return $resource;
  }

  /**
   * @param string|null $folder
   * @param string|null $name
   *
   * @return string
   *
   * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
   */
  public function dump(?string $folder = null, ?string $name = null): string {
    $folder = rtrim($folder, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    $path = $folder.($name ?: $this->getName().'.xlsx');

    $writer = new Xlsx($this->spreadsheet);
    $writer->save($path);

    return $path;
  }

}
