<?php

namespace HBM\DatagridBundle\Service;

use HBM\DatagridBundle\Model\TableCell;

class TableCellHelper {

  /**
   * @param string $alias
   * @param int $visibility
   * @param array $options
   *
   * @return TableCell
   */
  public function name(string $alias, int $visibility = TableCell::VISIBLE_ALL, array $options = []) : TableCell {
    return new TableCell('name', 'Name', NULL, $visibility, array_merge([
      'sort_key' => $alias.'.name',
    ], $options));
  }

  /**
   * @param string $alias
   * @param callable|null $value
   * @param int $visibility
   * @param array $options
   *
   * @return TableCell
   */
  public function group(string $alias, callable $value = NULL, int $visibility = TableCell::VISIBLE_ALL, array $options = []) : TableCell {
    $defaultOptions = [
      'sort_key' => $alias.'.group'
    ];
    if ($value) {
      $defaultOptions['value'] = $value;
    }

    return new TableCell('group', 'Gruppe', NULL, $visibility, array_merge($defaultOptions, $options));
  }

  /**
   * @param string|null $alias
   * @param string $template
   * @param string $headline
   * @param string|null $field
   * @param string|null $thClass
   * @param int $visibility
   * @param array $options
   *
   * @return TableCell
   */
  public function icon(?string $alias, string $template = 'partials/table-column-status.html.twig', string $headline = 'Status', ?string $field = NULL, ?string $thClass = 'fixed-10-center', int $visibility = TableCell::VISIBLE_BOTH, array $options = []) : TableCell {
    $defaultOptions = [
      'th_attr' => ['class' => $thClass],
      'td_attr' => ['class' => 'text-center'],
      'template' => $template,
    ];
    if ($alias && $field) {
      $defaultOptions['sort_key'] = $alias.'.'.$field;
    }

    return new TableCell($field, $headline, NULL, $visibility, array_merge($defaultOptions, $options));
  }

  /**
   * @param string $alias
   * @param string $field
   * @param string $label
   * @param int $visibility
   * @param array $options
   *
   * @return TableCell
   */
  public function date(string $alias, string $field, string $label, int $visibility = TableCell::VISIBLE_EXTENDED_EX, array $options = []) : TableCell {
    return new TableCell($field, $label, NULL, $visibility, array_merge([
      'sort_key' => $alias.'.'.$field,
      'format' => 'd.m.Y',
    ], $options));
  }

  /**
   * @param string $alias
   * @param string $field
   * @param string $label
   * @param int $visibility
   * @param array $options
   *
   * @return TableCell
   */
  public function datetime(string $alias, string $field, string $label, int $visibility = TableCell::VISIBLE_EXTENDED_EX, array $options = []) : TableCell {
    return new TableCell($field, $label, NULL, $visibility, array_merge([
      'sort_key' => $alias.'.'.$field,
    ], $options));
  }

  /**
   * @param string $alias
   * @param int $visibility
   * @param array $options
   * @return TableCell
   */
  public function modified(string $alias, int $visibility = TableCell::VISIBLE_EXTENDED_EX, array $options = []) : TableCell {
    return $this->datetime($alias, 'modified', 'Geändert', $visibility, $options);
  }

  /**
   * @param string $alias
   * @param int $visibility
   * @param array $options
   * @return TableCell
   */
  public function created(string $alias, int $visibility = TableCell::VISIBLE_EXTENDED_EX, array $options = []) : TableCell {
    return $this->datetime($alias, 'created', 'Erstellt', $visibility, $options);
  }

  /**
   * @param string $alias
   * @param int $visibility
   * @param array $options
   *
   * @return TableCell
   */
  public function creator(string $alias, int $visibility = TableCell::VISIBLE_EXTENDED, array $options = []) : TableCell {
    return new TableCell('creator', 'Ersteller', NULL, $visibility, array_merge([
      'sort_key' => $alias.'.creator',
      'template' => '@HBMDatagrid/partials/datagrid-cells/creator.html.twig',
    ], $options));
  }

  /**
   * @param string $alias
   * @param int $visibilityList
   * @param int $visibilityExport
   * @param array $options
   *
   * @return array
   */
  public function creatorCells(string $alias, int $visibilityList = TableCell::VISIBLE_EXTENDED, int $visibilityExport = TableCell::VISIBLE_EXPORT, array $options = []) : array {
    return [
      'creator' => $this->creator($alias, $visibilityList, $options),
      'creator_name' => new TableCell('creatorName', 'Ersteller (Name)', NULL, $visibilityExport),
      'creator_email' => new TableCell('creatorEmail', 'Ersteller (E-Mail)', NULL, $visibilityExport),
    ];
  }

  /**
   * @param string $key
   * @param string $label
   * @param array|callable $params
   * @param string|null $tdClass
   * @param string|null $thClass
   *
   * @return TableCell
   */
  public function list(string $key, string $label, $params = [], ?string $tdClass = 'list-group-cell-narrow', ?string $thClass = NULL, array $options = []) : TableCell {
    return new TableCell($key, $label, NULL, TableCell::VISIBLE_BOTH, array_merge([
      'template' => '@HBMDatagrid/partials/datagrid-cells/list.html.twig',
      'template_params' => $params,
      'th_attr' => ['class' => $thClass],
      'td_attr' => ['class' => $tdClass],
    ], $options));
  }

}
