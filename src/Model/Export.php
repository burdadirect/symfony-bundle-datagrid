<?php

namespace HBM\DatagridBundle\Model;

use Symfony\Component\HttpFoundation\Response;

abstract class Export extends Formatter {

  public const CONTENT_TYPE = '';
  public const EXTENSION = '';

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

  /**
   * @return string
   */
  public function contenType(): string {
    return static::CONTENT_TYPE;
  }

  /**
   * @return string
   */
  public function filename(): string {
    return $this->getName().(static::EXTENSION ? '.'.static::EXTENSION : '');
  }

  /****************************************************************************/
  /* ABSTRACT                                                                 */
  /****************************************************************************/

  abstract public function addHeader();

  abstract public function addRow($obj);

  /****************************************************************************/

  abstract public function response(): Response;

  abstract public function stream();

  abstract public function dump(?string $folder = null, ?string $name = null): string;

}
