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
   * @var string
   */
  protected $hash;

  /**
   * Route constructor.
   *
   * @param $name
   * @param array $defaults
   * @param string $hash
   */
  public function __construct($name = NULL, $defaults = array(), $hash = NULL) {
    $this->name = $name;
    $this->defaults = $defaults;
    $this->hash = $hash;
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

  public function setHash($hash) {
    $this->hash = $hash;
  }

  public function getHash($prefix = '') {
    if ($this->hash) {
      return $prefix.$this->hash;
    }

    return NULL;
  }

  /* CUSTOM *****************************************************************/

  public function getMerged() {
    return $this->getDefaults();
  }

  public function __toString() {
    return 'ROUTE: ' . $this->name . '(' . json_encode($this->getDefaults()) . ')';
  }
}
