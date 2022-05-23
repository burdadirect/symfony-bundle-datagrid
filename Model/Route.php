<?php

namespace HBM\DatagridBundle\Model;


class Route {

  protected ?string $name;

  protected array $defaults;

  protected ?string $hash;

  /**
   * Route constructor.
   *
   * @param string|null $name
   * @param array $defaults
   * @param string|null $hash
   */
  public function __construct(string $name = NULL, array $defaults = [], string $hash = NULL) {
    $this->name = $name;
    $this->defaults = $defaults;
    $this->hash = $hash;
  }

  /* GETTER/SETTER **********************************************************/

  public function setName($name) : void {
    $this->name = $name;
  }

  public function getName() : ?string {
    return $this->name;
  }

  public function setDefaults($defaults) : void {
    $this->defaults = $defaults;
  }

  public function getDefaults() : array {
    return $this->defaults;
  }

  public function setHash($hash) : void {
    $this->hash = $hash;
  }

  public function getHash($prefix = '') : ?string {
    if ($this->hash) {
      return $prefix.$this->hash;
    }

    return NULL;
  }

  /* CUSTOM *****************************************************************/

  public function getMerged() : array {
    return $this->getDefaults();
  }

  public function __toString() {
    return 'ROUTE: ' . $this->name . '(' . json_encode($this->getDefaults()) . ')';
  }
}
