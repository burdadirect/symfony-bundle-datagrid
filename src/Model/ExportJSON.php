<?php

namespace HBM\DatagridBundle\Model;

use Symfony\Component\HttpFoundation\Response;

class ExportJSON extends Export
{
    public const CONTENT_TYPE = 'application/json';
    public const EXTENSION    = 'json';

    protected array $lines = [];

    protected array $labels = [];

    public function addHeader(): void
    {
        $this->labels = [];

        /** @var TableCell $cell */
        foreach ($this->getCells() as $index => $cell) {
            if ($cell->isVisibleExport()) {
                $this->labels[$index] = $this->prepareLabel($cell->getLabelText());
            }
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function addRow($obj): void
    {
        $line = [];

        $row = \count($this->lines);

        /** @var TableCell $cell */
        $column = 0;
        foreach ($this->getCells() as $index => $cell) {
            if ($cell->isVisibleExport()) {
                $value = $cell->setFormatter($this)->getValue($obj, $column, $row);

                $line[$this->labels[$index]] = $value;

                ++$column;
            }
        }

        $this->lines[] = $line;
    }

    protected function formatCellValueArray(TableCell $cell, array $value, ?string $separator = ','): array
    {
        return $value;
    }

    /**
     * @throws \InvalidArgumentException|\JsonException
     */
    public function response(): Response
    {
        $content = json_encode($this->lines, JSON_THROW_ON_ERROR);

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
     * @throws \JsonException
     *
     * @return null|resource|string
     */
    public function stream()
    {
        $resource = fopen('php://temp', 'wb+');
        fwrite($resource, json_encode($this->lines, JSON_THROW_ON_ERROR));

        return $resource ?: null;
    }

    /**
     * @throws \JsonException
     */
    public function dump(string $folder = null, string $name = null): string
    {
        $folder = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path   = $folder . ($name ?: $this->getName() . '.json');

        file_put_contents($path, json_encode($this->lines, JSON_THROW_ON_ERROR));

        return $path;
    }
}
