<?php

namespace HBM\DatagridBundle\Model;

use Symfony\Component\HttpFoundation\Response;

class ExportCSV extends Export
{
    public const CONTENT_TYPE = 'text/csv';
    public const EXTENSION    = 'csv';

    protected array $lines = [];

    public function addHeader(): void
    {
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
     * @throws \InvalidArgumentException
     */
    public function addRow($obj): void
    {
        $line = [];

        $row = \count($this->lines) - 1;

        /** @var TableCell $cell */
        $column = 0;
        foreach ($this->getCells() as $cell) {
            if ($cell->isVisibleExport()) {
                $value = $cell->setFormatter($this)->getValue($obj, $column, $row);

                $line[] = $this->encloseValue($value);

                ++$column;
            }
        }

        $this->lines[] = implode(';', $line);
    }

    private function encloseValue($value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function response(): Response
    {
        $content = utf8_decode(implode("\n", $this->lines));

        return new Response($content, Response::HTTP_OK, [
          'Pragma'              => 'no-cache',
          'Cache-Control'       => 'Cache-Control: must-revalidate, post-check=0, pre-check=0',
          'Last-Modified'       => gmdate('D, d M Y H:i:s') . ' GMT',
          'Content-Type'        => $this->contenType(),
          'Content-Disposition' => 'attachment; filename="' . $this->filename() . '"',
          'Content-Length'      => \strlen($content),
          'Accept-Ranges'       => 'bytes',
        ]);
    }

    /**
     * @return null|resource|string
     */
    public function stream()
    {
        $resource = fopen('php://temp', 'wb+');
        fwrite($resource, utf8_decode(implode("\n", $this->lines)));

        return $resource ?: null;
    }

    public function dump(string $folder = null, string $name = null): string
    {
        $folder = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path   = $folder . ($name ?: $this->getName() . '.csv');

        file_put_contents($path, utf8_decode(implode("\n", $this->lines)));

        return $path;
    }
}
