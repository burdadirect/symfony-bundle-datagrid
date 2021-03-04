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

  abstract public function init();

  abstract public function finish();

  abstract public function addHeader();

  abstract public function addRow($obj);

  abstract public function output();

}
