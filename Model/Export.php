<?php

namespace HBM\DatagridBundle\Model;

abstract class Export extends Formatter {

  protected string $name;

  protected array $cells = [];

  /****************************************************************************/
  /* GETTER/SETTER                                                            */
  /****************************************************************************/

  public function setName(string $name): void {
    $this->name = $name;
  }

  public function getName(): string {
    return $this->name;
  }

  public function setCells($cells): void {
    $this->cells = $cells;
  }

  public function getCells(): array {
    return $this->cells;
  }

  /****************************************************************************/
  /* BASIC                                                                    */
  /****************************************************************************/

  public function init() : void {
  }

  public function finish() : void {
  }

  /**
   * @param $label
   *
   * @return string
   */
  protected function prepareLabel($label) : string {
    return html_entity_decode(strip_tags($label));
  }

  /**
   * @param TableCell $cell
   * @param $value
   *
   * @return mixed
   */
  public function formatCellValueString(TableCell $cell, $value) {
    if ($cell->getOption('strip_tags', true)) {
      return strip_tags($value);
    }

    return $value;
  }

  /****************************************************************************/
  /* ABSTRACT                                                                 */
  /****************************************************************************/

  abstract public function addHeader();

  abstract public function addRow($obj);

  abstract public function output();

}
