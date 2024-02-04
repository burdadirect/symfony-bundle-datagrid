<?php

namespace HBM\DatagridBundle\Service;

use HBM\DatagridBundle\Model\TableCell;

class TableCellHelper
{
    public function name(string $alias, int $visibility = TableCell::VISIBLE_ALL, array $options = []): TableCell
    {
        return new TableCell('name', 'Name', null, $visibility, array_merge([
          'sort_key' => $alias . '.name',
        ], $options));
    }

    public function group(string $alias, callable $value = null, int $visibility = TableCell::VISIBLE_ALL, array $options = []): TableCell
    {
        $defaultOptions = [
          'sort_key' => $alias . '.group',
        ];

        if ($value) {
            $defaultOptions['value'] = $value;
        }

        return new TableCell('group', 'Gruppe', null, $visibility, array_merge($defaultOptions, $options));
    }

    public function icon(?string $alias, string $template = 'partials/table-column-status.html.twig', string $headline = 'Status', string $field = null, ?string $thClass = 'fixed-10-center', int $visibility = TableCell::VISIBLE_BOTH, array $options = []): TableCell
    {
        $defaultOptions = [
          'th_attr'  => ['class' => $thClass],
          'td_attr'  => ['class' => 'text-center'],
          'template' => $template,
        ];

        if ($alias && $field) {
            $defaultOptions['sort_key'] = $alias . '.' . $field;
        }

        return new TableCell($field, $headline, null, $visibility, array_merge($defaultOptions, $options));
    }

    public function date(string $alias, string $field, string $label, int $visibility = TableCell::VISIBLE_EXTENDED_EX, array $options = []): TableCell
    {
        return new TableCell($field, $label, null, $visibility, array_merge([
          'sort_key' => $alias . '.' . $field,
          'format'   => 'd.m.Y',
        ], $options));
    }

    public function datetime(string $alias, string $field, string $label, int $visibility = TableCell::VISIBLE_EXTENDED_EX, array $options = []): TableCell
    {
        return new TableCell($field, $label, null, $visibility, array_merge([
          'sort_key' => $alias . '.' . $field,
        ], $options));
    }

    public function modified(string $alias, int $visibility = TableCell::VISIBLE_EXTENDED_EX, array $options = []): TableCell
    {
        return $this->datetime($alias, 'modified', 'Geändert', $visibility, $options);
    }

    public function created(string $alias, int $visibility = TableCell::VISIBLE_EXTENDED_EX, array $options = []): TableCell
    {
        return $this->datetime($alias, 'created', 'Erstellt', $visibility, $options);
    }

    public function datetimeCells(string $alias, int $visibility = TableCell::VISIBLE_EXTENDED_EX, array $options = [], array $templateParams = []): array
    {
        return [
          'createdAndModified' => new TableCell('createdAndModified', '<span class="text-normal font-weight-bold">Zeitpunkte</span><br />', null, $visibility, array_merge([
            'th_attr'         => ['class' => 'text-center small'],
            'template'        => '@HBMDatagrid/partials/datagrid-cells/datetimes.html.twig',
            'template_params' => $templateParams,
            'sort_key'        => [$alias . '.created' => 'Erstellt', $alias . '.modified' => 'Geändert'],
            'sort_key_sep'    => '&nbsp;|&nbsp;',
          ], $options)),
          'createdExport'  => $this->datetime($alias, 'created', 'Erstellt', TableCell::VISIBLE_EXPORT),
          'modifiedExport' => $this->datetime($alias, 'modified', 'Geändert', TableCell::VISIBLE_EXPORT),
        ];
    }

    public function creator(string $alias, int $visibility = TableCell::VISIBLE_EXTENDED, array $options = []): TableCell
    {
        return new TableCell('creator', 'Ersteller', null, $visibility, array_merge([
          'sort_key' => $alias . '.creator',
          'template' => '@HBMDatagrid/partials/datagrid-cells/creator.html.twig',
        ], $options));
    }

    public function creatorCells(string $alias, int $visibilityList = TableCell::VISIBLE_EXTENDED, int $visibilityExport = TableCell::VISIBLE_EXPORT, array $options = []): array
    {
        return [
          'creator'       => $this->creator($alias, $visibilityList, $options),
          'creator_name'  => new TableCell('creatorName', 'Ersteller (Name)', null, $visibilityExport),
          'creator_email' => new TableCell('creatorEmail', 'Ersteller (E-Mail)', null, $visibilityExport),
        ];
    }

    public function list(string $key, string $label, array|callable $params = [], ?string $tdClass = 'list-group-cell-narrow', string $thClass = null, array $options = []): TableCell
    {
        return new TableCell($key, $label, null, TableCell::VISIBLE_BOTH, array_merge([
          'template'        => '@HBMDatagrid/partials/datagrid-cells/list.html.twig',
          'template_params' => $params,
          'th_attr'         => ['class' => $thClass],
          'td_attr'         => ['class' => $tdClass],
        ], $options));
    }
}
