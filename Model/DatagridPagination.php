<?php

namespace HBM\DatagridBundle\Model;

class DatagridPagination {

  /**
   * @var Datagrid
   */
  private $datagrid;

  /* CONFIG *******************************************************************/

  /**
   * @var integer
   */
  private $maxLinksPerPage;

  /**
   * @var boolean
   */
  private $showFirst;

  /**
   * @var boolean
   */
  private $showPrev;

  /**
   * @var boolean
   */
  private $showNext;

  /**
   * @var boolean
   */
  private $showLast;

  /**
   * @var boolean
   */
  private $showSep;

  /**
   * @var string
   */
  private $template;

  /* ROUTING ******************************************************************/

  /**
   * @var Route
   */
  private $route;

  /* LINKS ********************************************************************/

  /**
   * @var array;
   */
  private $links = array();

  /**
   * @var RouteLink
   */
  private $linkFirst;

  /**
   * @var RouteLink
   */
  private $linkPrev;

  /**
   * @var RouteLink
   */
  private $linkNext;

  /**
   * @var RouteLink
   */
  private $linkLast;

  /* NUMBERS ******************************************************************/

  /**
   * @var integer
   */
  private $offset;

  /**
   * @var integer
   */
  private $number;

  /**
   * @var integer
   */
  private $numberTotal;

  /**
   * @var integer
   */
  private $numberFrom;

  /**
   * @var integer
   */
  private $numberThru;

  /* PAGES ********************************************************************/

  /**
   * @var integer
   */
  private $pageCurrent = 1;

  /**
   * @var integer
   */
  private $pageMax;

  /**
   * @var integer
   */
  private $pageFrom;

  /**
   * @var integer
   */
  private $pageThru;

  /* CONSTRUCTOR **************************************************************/

  public function __construct($config) {
    $this->setMaxLinksPerPage($config['pagination']['max_links_per_page']);

    $this->setShowFirst($config['pagination']['show_first']);
    $this->setShowPrev($config['pagination']['show_prev']);
    $this->setShowNext($config['pagination']['show_next']);
    $this->setShowLast($config['pagination']['show_last']);
    $this->setShowSep($config['pagination']['show_sep']);
    $this->setTemplate($config['pagination']['template']);
  }

  /* GETTER/SETTER ************************************************************/

  public function setDatagrid($datagrid) {
    $this->datagrid = $datagrid;
  }

  public function getDatagrid() {
    return $this->datagrid;
  }


  public function setMaxLinksPerPage($maxLinksPerPage) {
    $this->maxLinksPerPage = $maxLinksPerPage;
  }

  public function getMaxLinksPerPage() {
    return $this->maxLinksPerPage;
  }

  public function setShowFirst($showFirst) {
    $this->showFirst = $showFirst;
  }

  public function getShowFirst() {
    return $this->showFirst;
  }

  public function setShowPrev($showPrev) {
    $this->showPrev = $showPrev;
  }

  public function getShowPrev() {
    return $this->showPrev;
  }

  public function setShowNext($showNext) {
    $this->showNext = $showNext;
  }

  public function getShowNext() {
    return $this->showNext;
  }

  public function setShowLast($showLast) {
    $this->showLast = $showLast;
  }

  public function getShowLast() {
    return $this->showLast;
  }

  public function setShowSep($showSep) {
    $this->showSep = $showSep;
  }

  public function getShowSep() {
    return $this->showSep;
  }

  public function setTemplate($template) {
    $this->template = $template;
  }

  public function getTemplate() {
    return $this->template;
  }


  public function setRoute(Route $route) {
    $this->route = $route;
  }

  public function getRoute() {
    return $this->route;
  }


  public function setLinks($links) {
    $this->links = $links;
  }

  public function getLinks() {
    return $this->links;
  }

  public function addLink(RouteLink $link) {
    $this->links[] = $link;
  }

  public function setLinkFirst($linkFirst) {
    $this->linkFirst = $linkFirst;
  }

  public function getLinkFirst() {
    return $this->linkFirst;
  }

  public function setLinkPrev($linkPrev) {
    $this->linkPrev = $linkPrev;
  }

  public function getLinkPrev() {
    return $this->linkPrev;
  }

  public function setLinkNext($linkNext) {
    $this->linkNext = $linkNext;
  }

  public function getLinkNext() {
    return $this->linkNext;
  }

  public function setLinkLast($linkLast) {
    $this->linkLast = $linkLast;
  }

  public function getLinkLast() {
    return $this->linkLast;
  }


  public function setOffset($offset) {
    $this->offset = $offset;
  }

  public function getOffset() {
    return $this->offset;
  }

  public function setNumber($number) {
    $this->number = $number;
  }

  public function getNumber() {
    return $this->number;
  }

  public function setNumberTotal($numberTotal) {
    $this->numberTotal = $numberTotal;
  }

  public function getNumberTotal() {
    return $this->numberTotal;
  }

  public function setNumberFrom($numberFrom) {
    $this->numberFrom = $numberFrom;
  }

  public function getNumberFrom() {
    return $this->numberFrom;
  }

  public function setNumberThru($numberThru) {
    $this->numberThru = $numberThru;
  }

  public function getNumberThru() {
    return $this->numberThru;
  }


  public function setPageCurrent($pageCurrent) {
    $this->pageCurrent = $pageCurrent;
  }

  public function getPageCurrent() {
    return $this->pageCurrent;
  }

  public function setPageMax($pageMax) {
    $this->pageMax = $pageMax;
  }

  public function getPageMax() {
    return $this->pageMax;
  }

  public function setPageFrom($pageFrom) {
    $this->pageFrom = $pageFrom;
  }

  public function getPageFrom() {
    return $this->pageFrom;
  }

  public function setPageThru($pageThru) {
    $this->pageThru = $pageThru;
  }

  public function getPageThru() {
    return $this->pageThru;
  }

  /* CUSTOM *****************************************************************/

  public function createLink($page) {
    $routeLink = new RouteLink([$this->datagrid->getParamNameCurrentPage() => $page], $this->getRoute());
    $routeLink->setValue($page);

    return $routeLink;
  }

  public function __toString() {
    $string = '';
    $string .= 'PAGINATION-VARS:' . "\n";
    $string .= 'maxLinksPerPage: ' . $this->getMaxLinksPerPage() . "\n";
    $string .= 'showFirst: ' . $this->getShowFirst() . "\n";
    $string .= 'showPrev: ' . $this->getShowPrev() . "\n";
    $string .= 'showNext: ' . $this->getShowNext() . "\n";
    $string .= 'showLast: ' . $this->getShowLast() . "\n";
    $string .= 'showSep: ' . $this->getShowSep() . "\n";
    $string .= "\n";
    $string .= 'PAGINATION-CALCULATED:' . "\n";
    $string .= 'offset: ' . $this->getOffset() . "\n";
    $string .= 'number: ' . $this->getNumber() . "\n";
    $string .= 'numberTotal: ' . $this->getNumberTotal() . "\n";
    $string .= 'numberFrom: ' . $this->getNumberFrom() . "\n";
    $string .= 'numberThru: ' . $this->getNumberThru() . "\n";
    $string .= 'pageCurrent: ' . $this->getPageCurrent() . "\n";
    $string .= 'pageMax: ' . $this->getPageMax() . "\n";
    $string .= 'pageFrom: ' . $this->getPageFrom() . "\n";
    $string .= 'pageThru: ' . $this->getPageThru() . "\n";
    $string .= "\n";
    $string .= 'PAGINATION-ROUTE:' . "\n";
    $string .= $this->route . "\n";
    $string .= "\n";
    $string .= 'PAGINATION-LINKS:' . "\n";
    foreach ($this->links as $link) {
      $string .= $link . "\n";
    }
    $string .= 'PAGINATION-LINK-FIRST:' . "\n";
    $string .= $this->linkFirst . "\n";
    $string .= "\n";
    $string .= 'PAGINATION-LINK-PREV:' . "\n";
    $string .= $this->linkPrev . "\n";
    $string .= "\n";
    $string .= 'PAGINATION-LINK-NEXT:' . "\n";
    $string .= $this->linkNext . "\n";
    $string .= "\n";
    $string .= 'PAGINATION-LINK-LAST:' . "\n";
    $string .= $this->linkLast . "\n";
    $string .= "\n";

    return $string;
  }

}
