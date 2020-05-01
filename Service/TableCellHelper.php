<?php

namespace HBM\DatagridBundle\Service;

use HBM\DatagridBundle\Model\TableCell;

class DatagridCellHelper {

  /**
   * @param string $alias
   * @param int $visibility
   *
   * @return TableCell
   */
  public function name(string $alias, int $visibility = TableCell::VISIBLE_ALL) : TableCell {
    return new TableCell('name', 'Name', NULL, $visibility, [
      'sort_key' => $alias.'.name',
    ]);
  }

  /**
   * @param string $alias
   * @param callable $value
   * @param int $visibility
   *
   * @return TableCell
   */
  public function group(string $alias, callable $value = NULL, int $visibility = TableCell::VISIBLE_ALL) : TableCell {
    $options = [
      'sort_key' => $alias.'.group'
    ];
    if ($value) {
      $options['value'] = $value;
    }

    return new TableCell('group', 'Gruppe', NULL, $visibility, $options);
  }

  /**
   * @param string $alias
   * @param string $template
   * @param string $headline
   * @param string $field
   * @param string $thClass
   * @param int $visibility
   *
   * @return TableCell
   */
  public function icon(string $alias, string $template = 'partials/table-column-status.html.twig', string $headline = 'Status', string $field = NULL, string $thClass = 'fixed-10-center', int $visibility = TableCell::VISIBLE_BOTH) : TableCell {
    $options = [
      'th_attr' => ['class' => $thClass],
      'td_attr' => ['class' => 'text-center'],
      'template' => $template,
    ];
    if ($field) {
      $options['sort_key'] = $alias.'.'.$field;
    }

    return new TableCell($field, $headline, NULL, $visibility, $options);
  }

  /**
   * @param string $alias
   * @param string $field
   * @param string $label
   * @param int $visibility
   *
   * @return TableCell
   */
  public function date(string $alias, string $field, string $label, int $visibility = TableCell::VISIBLE_EXTENDED_EX) : TableCell {
    return new TableCell($field, $label, NULL, $visibility, [
      'sort_key' => $alias.'.'.$field,
      'format' => 'd.m.Y',
    ]);
  }

  /**
   * @param string $alias
   * @param string $field
   * @param string $label
   * @param int $visibility
   *
   * @return TableCell
   */
  public function datetime(string $alias, string $field, string $label, int $visibility = TableCell::VISIBLE_EXTENDED_EX) : TableCell {
    return new TableCell($field, $label, NULL, $visibility, [
      'sort_key' => $alias.'.'.$field,
    ]);
  }

  /**
   * @param string $alias
   * @param int $visibility
   *
   * @return TableCell
   */
  public function modified(string $alias, int $visibility = TableCell::VISIBLE_EXTENDED_EX) : TableCell {
    return $this->datetime($alias, 'modified', 'Geändert', $visibility);
  }

  /**
   * @param string $alias
   * @param int $visibility
   *
   * @return TableCell
   */
  public function created(string $alias, int $visibility = TableCell::VISIBLE_EXTENDED_EX) : TableCell {
    return $this->datetime($alias, 'created', 'Erstellt', $visibility);
  }

  /**
   * @param string $alias
   * @param int $visibility
   *
   * @return TableCell
   */
  public function creator(string $alias, int $visibility = TableCell::VISIBLE_EXTENDED) : TableCell {
    return new TableCell('creator', 'Ersteller', NULL, $visibility, [
      'sort_key' => $alias.'.creator',
      'template' => '@HBMDatagrid/partials/datagrid-cells/creator.html.twig',
    ]);
  }

  /**
   * @param string $alias
   * @param int $visibilityList
   * @param int $visibilityExport
   *
   * @return array
   */
  public function creatorCells(string $alias, int $visibilityList = TableCell::VISIBLE_EXTENDED, int $visibilityExport = TableCell::VISIBLE_EXPORT) : array {
    return [
      'creator' => $this->creator($alias, $visibilityList),
      'creator_name' => new TableCell('creatorName', 'Ersteller (Name)', NULL, $visibilityExport, []),
      'creator_email' => new TableCell('creatorEmail', 'Ersteller (E-Mail)', NULL, $visibilityExport, []),
    ];
  }

  /**
   * @param string $key
   * @param string $label
   * @param array|callable $params
   * @param string $thClass
   *
   * @return TableCell
   */
  public function list(string $key, string $label, $params = [], $thClass = 'fixed-10-center list-group-cell-narrow') : TableCell {
    return new TableCell($key, $label, NULL, TableCell::VISIBLE_BOTH, [
      'template' => '@HBMDatagrid/partials/datagrid-cells/list.html.twig',
      'template_params' => $params,
      'th_attr' => ['class' => $thClass],
      'td_attr' => ['class' => $thClass],
    ]);
  }

}
