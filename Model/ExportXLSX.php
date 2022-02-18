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

  public const CONTENT_TYPE = 'application/vnd.ms-excel';
  public const EXTENSION = 'xlsx';

  protected Spreadsheet $spreadsheet;

  protected Worksheet $worksheet;

  protected int $row = 1;

  protected array $columnsWidths = [];

  protected ?string $password = null;

  /**
   * Set password.
   *
   * @param string|null $password
   *
   * @return self
   */
  public function setPassword(?string $password): self {
    $this->password = $password;

    return $this;
  }

  /**
   * Get password.
   *
   * @return string|null
   */
  public function getPassword(): ?string {
    return $this->password;
  }

  /**
   * @return Spreadsheet
   */
  public function getSpreadsheet(): Spreadsheet {
    return $this->spreadsheet;
  }

  /**
   * @return Worksheet
   */
  public function getWorksheet(): Worksheet {
    return $this->worksheet;
  }

  public function init() : void {
    $this->spreadsheet = new Spreadsheet();
    $this->spreadsheet->getProperties()->setTitle('Datagrid-Export');

    $this->worksheet = $this->spreadsheet->setActiveSheetIndex(0);
    $this->worksheet->setTitle('Export');
  }

  /**
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   */
  public function finish() : void {
    foreach ($this->columnsWidths as $columnName => $columnWidth) {
      $columnWidthCalculated = $columnWidth;
      if (substr($columnWidthCalculated, -2) === 'px') {
        $columnWidthCalculated = SharedDrawing::pixelsToCellDimension((int)substr($columnWidthCalculated, 0, -2), $this->getSpreadsheet()->getDefaultStyle()->getFont());
      }
      $this->getWorksheet()->getColumnDimension($columnName)->setWidth($columnWidthCalculated);
    }
  }

  public function addHeader() : void {
    /** @var TableCell $cell */
    $column = 1;
    foreach ($this->getCells() as $cell) {
      if ($cell->isVisibleExport()) {
        $this->getWorksheet()->setCellValueByColumnAndRow($column, $this->row, $this->prepareLabel($cell->getLabelText()));
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

        $this->getWorksheet()->setCellValueByColumnAndRow($column, $this->row, $value);

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

    $drawing->setWorksheet($this->getWorksheet());

    $this->getWorksheet()->getRowDimension($row)->setRowHeight(SharedDrawing::pixelsToPoints($imageInfo[1] + 2*$columnOffset));

    return TRUE;
  }

  /**
   * @return Xlsx
   */
  protected function prepareWriter(): Xlsx {
    $spreadsheet = $this->getSpreadsheet();
    if ($password = $this->getPassword()) {
      $security = $spreadsheet->getSecurity();
      $security->setLockWindows(true);
      $security->setLockStructure(true);
      $security->setWorkbookPassword($password);
    }

    return new Xlsx($spreadsheet);
  }

  /**
   * @return StreamedResponse
   */
  public function response(): StreamedResponse {
    $writer = $this->prepareWriter();

    $callable = function() use ($writer) {
      $writer->save('php://output');
    };

    return new StreamedResponse($callable, 200, [
      'Pragma' => 'no-cache',
      'Cache-Control' => 'Cache-Control: must-revalidate, post-check=0, pre-check=0',
      'Last-Modified' => gmdate('D, d M Y H:i:s').' GMT',
      'Content-Type' => $this->contenType(),
      'Content-Disposition' => 'attachment; filename="'.$this->filename().'"',
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

    $writer = $this->prepareWriter();
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
    $path = $folder.($name ?: $this->filename());

    $writer = $this->prepareWriter();
    $writer->save($path);

    return $path;
  }

}
