<?php

namespace HBM\DatagridBundle\Model;


class Route {

  /**
   * @var string
   */
  protected $name;

  /**
   * @var array
   */
  protected $defaults = array();

  /**
   * Route constructor.
   *
   * @param $name
   * @param array $defaults
   */
  public function __construct($name = NULL, $defaults = array()) {
    $this->name = $name;
    $this->defaults = $defaults;
  }

  /* GETTER/SETTER **********************************************************/

  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }

  public function setDefaults($defaults) {
    $this->defaults = $defaults;
  }

  public function getDefaults() {
    return $this->defaults;
  }

  /* CUSTOM *****************************************************************/

  public function getMerged() {
    return $this->getDefaults();
  }

  public function __toString() {
    return 'ROUTE: ' . $this->name . '(' . json_encode($this->getDefaults()) . ')';
  }
}
