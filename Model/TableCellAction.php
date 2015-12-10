<?php

namespace HBM\DatagridBundle\Model;

class TableCellAction extends TableCell {

  /**
   * @var Route
   */
  private $route;

  protected $validOptions = [
    'value' => 'string|callback',
    'th_attr' => 'string|array',
    'td_attr' => 'string|array',
    'a_attr' => 'string|array',
    'sort_key' => 'string',
    'params' => 'array|callback',
    'template' => 'string|callback',
    'templateParams' => 'array',
    'format' => 'string',
  ];

  public function __construct($key, $label, $route, $options = []) {
    $this->key = $key;
    $this->label = $label;
    $this->route = $route;

    $this->setOptions($options);
  }

  /* GETTER/SETTER ************************************************************/

  public function setRoute($route) {
    $this->route = $route;
  }

  public function getRoute() {
    return $this->route;
  }

  /* CUSTOM *****************************************************************/

  public function getTemplate($column, $row, $obj, $default = 'HBMDatagridBundle:Datagrid/table-cell:table-cell-action.html.twig') {
    return parent::getTemplate($column, $row, $obj, $default);
  }

  public function getLink($column, $row, $obj) {
    return new RouteLink($this->getParams($column, $row, $obj), $this->getRoute());
  }

  public function getParams($column, $row, $obj) {
    if ($this->hasOption('params')) {
      $params = $this->getOption('params');
      if (is_string($params)) {
        return $params;
      } else {
        if (is_callable($params)) {
          return $params($column, $row, $obj);
        } else {
          throw new \Exception("How come?");
        }
      }
    }

    return array();
  }

}
