<?php

namespace HBM\DatagridBundle\Model;


class RouteLink extends Route {

  /**
   * @var array
   */
  private $params = array();

  /**
   * @var integer
   */
  private $value;

  /**
   * RouteLink constructor.
   *
   * @param $params
   * @param \HBM\DatagridBundle\Model\Route $route
   */
  public function __construct($params, Route $route) {
    $this->params = $params;

    if ($route !== NULL) {
      $this->name = $route->getName();
      $this->defaults = $route->getDefaults();
    }
  }

  /* GETTER/SETTER **********************************************************/

  public function setValue($value) {
    $this->value = $value;
  }

  public function getValue() {
    return $this->value;
  }

  public function setParams($params) {
    $this->params = $params;
  }

  public function getParams() {
    return $this->params;
  }

  /* CUSTOM *****************************************************************/

  public function getMerged() {
    return array_merge($this->getDefaults(), $this->getParams());
  }

  public function __toString() {
    return $this->name . '(' . json_encode($this->getMerged()) . ') [' . $this->value . ']';
  }

}
