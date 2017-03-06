<?php

namespace HBM\DatagridBundle\Model;

abstract class Export {

  /** @var string */
  protected $name;

  /** @var array */
  protected $cells;

  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }

  public function setCells($cells) {
    $this->cells = $cells;
  }

  public function getCells() {
    return $this->cells;
  }

  abstract function init();

  abstract function addHeader();

  abstract function addRow($obj);

  abstract function output();

}
