<?php

namespace HBM\DatagridBundle\Model;

use PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Drawing as SharedDrawing;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportXLSX extends Export
{
    public const CONTENT_TYPE = 'application/vnd.ms-excel';
    public const EXTENSION    = 'xlsx';

    protected Spreadsheet $spreadsheet;
    protected Worksheet $worksheet;

    protected int $row = 1;

    protected ?\Closure $callbackRow = null;
    protected ?\Closure $callbackFinish = null;

    public function getCallbackRow(): ?\Closure
    {
        return $this->callbackRow;
    }

    public function setCallbackRow(?\Closure $callbackRow): void
    {
        $this->callbackRow = $callbackRow;
    }

    public function getCallbackFinish(): ?\Closure
    {
        return $this->callbackFinish;
    }

    public function setCallbackFinish(?\Closure $callbackFinish): void
    {
        $this->callbackFinish = $callbackFinish;
    }

    public function getSpreadsheet(): Spreadsheet
    {
        return $this->spreadsheet;
    }

    public function getWorksheet(): Worksheet
    {
        return $this->worksheet;
    }

    public function init(): void
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->getProperties()->setTitle('Datagrid-Export');

        $this->worksheet = $this->spreadsheet->setActiveSheetIndex(0);
        $this->worksheet->setTitle('Export');
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function finish(): void
    {
        $callableFinish = $this->getCallbackFinish();
        if (is_callable($callableFinish)) {
            $callableFinish($this->getWorksheet());
        }
    }

    public function addHeader(): void
    {
        /** @var TableCell $cell */
        $column = 1;
        foreach ($this->getCells() as $cell) {
            if ($cell->isVisibleExport()) {
                $cellAddress = CellAddress::fromColumnAndRow($column, $this->row);
                $cellObject = $this->getWorksheet()->getCell($cellAddress);

                $cellObject->setValue($this->prepareLabel($this->translateLabel($cell->getLabelText())));
                $cellObject->getStyle()->getFont()->setBold(true);

                $columnWidth = $cell->getOption('xlsx_column_width');
                if ($columnWidth !== false) {
                    if (is_numeric($columnWidth)) {
                        $this->getWorksheet()->getColumnDimension($cellAddress->columnName())->setAutoSize(false)->setWidth($columnWidth);
                    } else {
                        $this->getWorksheet()->getColumnDimension($cellAddress->columnName())->setAutoSize(true);
                    }
                }

                $cellCallable = $cell->getOption('xlsx_header_callback');
                if (is_callable($cellCallable)) {
                    $cellCallable($cellObject);
                }

                ++$column;
            }
        }

        ++$this->row;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addRow($obj): void
    {
        $column = 1;

        /** @var TableCell $cell */
        foreach ($this->getCells() as $cell) {
            if ($cell->isVisibleExport()) {
                $value = $cell->setFormatter($this)->getValue($obj, $column, $this->row - 2);

                if ($value instanceof \SplFileInfo) {
                    if (!$this->setCellImageByColumnAndRow($cell, $column, $this->row, $value)) {
                        $value = $value->getBasename();
                    } else {
                        $value = null;
                    }
                }

                $cellAddress = CellAddress::fromColumnAndRow($column, $this->row);
                $cellObject = $this->getWorksheet()->getCell($cellAddress);
                $cellObject->setValue($value);

                $cellCallable = $cell->getOption('xlsx_cell_callback');
                if (is_callable($cellCallable)) {
                    $cellCallable($cellObject, $obj, $value);
                }

                ++$column;
            }
        }

        $callableRow = $this->getCallbackRow();
        if (is_callable($callableRow)) {
            $callableRow($this->getWorksheet(), $obj, $this->row);
        }

        ++$this->row;
    }

    protected function formatCellValueArray(TableCell $cell, array $value, ?string $separator = "\n")
    {
        return parent::formatCellValueArray($cell, $value, $separator);
    }

    protected function formatCellValueSplFileInfo(TableCell $cell, \SplFileInfo $value)
    {
        return $value;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function setCellImageByColumnAndRow(TableCell $cell, $column, $row, \SplFileInfo $file): bool
    {
        $columnName   = Coordinate::stringFromColumnIndex($column);
        $columnOffset = 10;

        $width = $cell->getOption('img_max_width');
        $height = $cell->getOption('img_max_height');

        $drawing = new Drawing();
        $drawing->setName($cell->getLabelText());
        $drawing->setDescription($file->getBasename());
        $drawing->setPath($file->getPathname());
        $drawing->setResizeProportional(true);
        if ($width && $height) {
            $drawing->setWidthAndHeight($width, $height);
        } elseif ($width) {
            $drawing->setWidth($width);
        } elseif ($height) {
            $drawing->setHeight($height);
        }
        $drawing->setOffsetX($columnOffset);
        $drawing->setOffsetY($columnOffset);
        $drawing->setCoordinates($columnName . $row);
        $drawing->setWorksheet($this->getWorksheet());

        $this->getWorksheet()->getRowDimension($row)->setRowHeight(SharedDrawing::pixelsToPoints($height + 2 * $columnOffset));
        $this->getWorksheet()->getColumnDimension($columnName)->setWidth(SharedDrawing::pixelsToPoints($width + 2 * $columnOffset));

        return true;
    }

    protected function prepareWriter(): Xlsx
    {
        return new Xlsx($this->getSpreadsheet());
    }

    public function response(): StreamedResponse
    {
        $writer = $this->prepareWriter();

        $callable = function () use ($writer) {
            $writer->save('php://output');
        };

        return new StreamedResponse($callable, Response::HTTP_OK, [
          'Pragma'              => 'no-cache',
          'Cache-Control'       => 'Cache-Control: must-revalidate, post-check=0, pre-check=0',
          'Last-Modified'       => gmdate('D, d M Y H:i:s') . ' GMT',
          'Content-Type'        => $this->contenType(),
          'Content-Disposition' => 'attachment; filename="' . $this->filename() . '"',
          'Accept-Ranges'       => 'bytes',
        ]);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     *
     * @return null|resource|string
     */
    public function stream()
    {
        $resource = fopen('php://temp', 'wb+');

        $writer = $this->prepareWriter();
        $writer->save($resource);

        return $resource;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function dump(string $folder = null, string $name = null): string
    {
        $folder = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path   = $folder . ($name ?: $this->filename());

        $writer = $this->prepareWriter();
        $writer->save($path);

        return $path;
    }
}
