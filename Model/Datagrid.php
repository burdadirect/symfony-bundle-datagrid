<?php

namespace HBM\DatagridBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

class Datagrid {

  /**
   * @var string
   */
  private $translationDomain;

  /**
   * @var array
   */
  private $bootstrap;

  /* SESSION ******************************************************************/

  /**
   * @var string
   */
  private $sessionPrefix;

  /**
   * @var array
   */
  private $sessionUseFor;

  /* DEFAULTS *****************************************************************/

  /**
   * @var integer
   */
  private $maxEntriesPerPage;

  /* PARAM NAMES **************************************************************/

  /**
   * @var string
   */
  private $paramNameCurrentPage;

  /**
   * @var string
   */
  private $paramNameMaxEntries;

  /**
   * @var string
   */
  private $paramNameSortation;

  /**
   * @var string
   */
  private $paramNameSearch;

  /**
   * @var string
   */
  private $paramNameExtended;

  /* QUERY ********************************************************************/

  /**
   * @var string
   */
  private $queryEncode;

  /* CONFIG *******************************************************************/

  /**
   * @var boolean
   */
  private $extended;

  /**
   * @var boolean
   */
  private $sort;

  /**
   * @var boolean
   */
  private $multiSort;

  /* CACHE ********************************************************************/

  /**
   * @var boolean
   */
  private $cacheEnabled;

  /**
   * @var integer
   */
  private $cacheSeconds;

  /**
   * @var string
   */
  private $cachePrefix;

  /* ROUTING ******************************************************************/

  /**
   * @var Route
   */
  private $route;

  /* COLUMNS/ROWS *************************************************************/

  /**
   * @var array
   */
  private $sortations = [];

  /**
   * @var array
   */
  private $cells;

  /**
   * @var callable|string|array
   */
  private $rowAttr = NULL;

  /**
   * @var \Doctrine\Common\Collections\Collection
   */
  private $results;

  /* PAGINATION/MENU **********************************************************/

  /**
   * @var DatagridPagination
   */
  private $pagination;

  /**
   * @var DatagridMenu
   */
  private $menu;

  /* CONSTRUCTOR **************************************************************/

  public function __construct($config) {
    $this->results = new ArrayCollection();

    $this->setTranslationDomain($config['translation_domain']);
    $this->setBootstrap($config['bootstrap']);

    $this->setSort($config['datagrid']['sort']);
    $this->setMultiSort($config['datagrid']['multi_sort']);
    $this->setMaxEntriesPerPage($config['datagrid']['max_entries_per_page']);

    $this->setCacheEnabled($config['cache']['enabled']);
    $this->setCacheSeconds($config['cache']['seconds']);
    $this->setCachePrefix($config['cache']['prefix']);

    $this->setParamNameCurrentPage($config['query']['param_names']['current_page']);
    $this->setParamNameMaxEntries($config['query']['param_names']['max_entries']);
    $this->setParamNameSortation($config['query']['param_names']['sortation']);
    $this->setParamNameSearch($config['query']['param_names']['search']);
    $this->setParamNameExtended($config['query']['param_names']['extended']);

    $this->setQueryEncode($config['query']['encode']);

    $this->setSessionPrefix($config['session']['prefix']);
    $this->setSessionUseFor($config['session']['use_for']);
  }

  /* GETTER/SETTER **********************************************************/

  public function setTranslationDomain($translationDomain) {
    $this->translationDomain = $translationDomain;
  }

  public function getTranslationDomain() {
    return $this->translationDomain;
  }

  public function setBootstrap($bootstrap) {
    $this->bootstrap = $bootstrap;
  }

  public function getBootstrap() {
    return $this->bootstrap;
  }


  public function setSessionPrefix($sessionPrefix) {
    $this->sessionPrefix = $sessionPrefix;
  }

  public function getSessionPrefix() {
    return $this->sessionPrefix;
  }

  public function setSessionUseFor($sessionUseFor) {
    $this->sessionUseFor = $sessionUseFor;
  }

  public function getSessionUseFor() {
    return $this->sessionUseFor;
  }


  public function setMaxEntriesPerPage($maxEntriesPerPage) {
    $this->maxEntriesPerPage = max(1, (int) $maxEntriesPerPage);
  }

  public function getMaxEntriesPerPage() {
    return $this->maxEntriesPerPage;
  }


  public function setCacheEnabled($cacheEnabled) {
    $this->cacheEnabled = $cacheEnabled;
  }

  public function getCacheEnabled() {
    return $this->cacheEnabled;
  }

  public function setCacheSeconds($cacheSeconds) {
    $this->cacheSeconds = $cacheSeconds;
  }

  public function getCacheSeconds() {
    return $this->cacheSeconds;
  }

  public function setCachePrefix($cachePrefix) {
    $this->cachePrefix = $cachePrefix;
  }

  public function getCachePrefix() {
    return $this->cachePrefix;
  }


  public function setParamNameCurrentPage($paramName) {
    $this->paramNameCurrentPage = $paramName;
  }

  public function getParamNameCurrentPage() {
    return $this->paramNameCurrentPage;
  }

  public function setParamNameMaxEntries($paramName) {
    $this->paramNameMaxEntries = $paramName;
  }

  public function getParamNameMaxEntries() {
    return $this->paramNameMaxEntries;
  }

  public function setParamNameSortation($paramName) {
    $this->paramNameSortation = $paramName;
  }

  public function getParamNameSortation() {
    return $this->paramNameSortation;
  }

  public function setParamNameSearch($paramName) {
    $this->paramNameSearch = $paramName;
  }

  public function getParamNameSearch() {
    return $this->paramNameSearch;
  }

  public function setParamNameExtended($paramName) {
    $this->paramNameExtended = $paramName;
  }

  public function getParamNameExtended() {
    return $this->paramNameExtended;
  }


  public function setQueryEncode($queryEncode) {
    $this->queryEncode = $queryEncode;
  }

  public function getQueryEncode() {
    return $this->queryEncode;
  }


  public function setExtended($extended) {
    $this->extended = (bool) $extended;
  }

  public function getExtended() {
    return $this->extended;
  }

  public function setSort($sort) {
    $this->sort = $sort;
  }

  public function getSort() {
    return $this->sort;
  }

  public function setMultiSort($multiSort) {
    $this->multiSort = $multiSort;
  }

  public function getMultiSort() {
    return $this->multiSort;
  }


  public function setRoute(Route $route) {
    $this->route = $route;
  }

  public function getRoute() {
    return $this->route;
  }


  public function setResults($results) {
    $this->results = $results;
  }

  public function getResults() {
    return $this->results;
  }

  public function setSortations($sortations) {
    $this->sortations = $sortations;
  }

  public function getSortations() {
    return $this->sortations;
  }

  public function setCells($cells) {
    $this->cells = $cells;
  }

  public function getCells() {
    return $this->cells;
  }

  public function setRowAttr($rowAttr) {
    $this->rowAttr = $rowAttr;
  }

  public function getRowAttr() {
    return $this->rowAttr;
  }

  /**
   * Set pagination
   *
   * @param DatagridPagination $pagination
   * @return Datagrid
   */
  public function setPagination($pagination) {
    $pagination->setDatagrid($this);

    $this->pagination = $pagination;

    return $this;
  }

  /**
   * Get pagination
   *
   * @return DatagridPagination
   */
  public function getPagination() {
    return $this->pagination;
  }

  /**
   * Set menu
   *
   * @param DatagridMenu $menu
   * @return Datagrid
   */
  public function setMenu($menu) {
    $menu->setDatagrid($this);

    $this->menu = $menu;

    return $this;
  }

  /**
   * Get menu
   *
   * @return DatagridMenu
   */
  public function getMenu() {
    return $this->menu;
  }

  /* CUSTOM *******************************************************************/

  public function isSorted($key) {
    if (isset($this->sortations[$key])) {
      return TRUE;
    }

    return FALSE;
  }

  public function getSortationDirection($key) {
    if (isset($this->sortations[$key])) {
      return strtolower($this->sortations[$key]);
    }

    return NULL;
  }

  public function parseRowAttr($obj, $row) {
    $rowAttr = $this->getRowAttr();
    if (is_callable($this->getRowAttr())) {
      $rowAttr = $this->getRowAttr()($obj, $row);
    }

    if (is_string($rowAttr)) {
      return $rowAttr;
    }
    if (is_array($rowAttr)) {
      return $this->getHtmlAttrString($rowAttr);
    }

    return NULL;
  }

  private function getHtmlAttrString($attributes) : string {
    $parts = [];
    foreach ($attributes as $key => $value) {
      $parts[] = $key . '="' . $value . '"';
    }

    return implode(' ', $parts);
  }

  public function __toString() {
    $string = '';
    $string .= 'DATAGRID-COLLECTIONS:' . "\n";
    $string .= is_countable($this->cells) ? \count($this->cells) : 'No' . ' Cells' . "\n";
    $string .= is_countable($this->results) ? \count($this->results) : 'No' . ' Results' . "\n";
    $string .= "\n";
    $string .= 'DATAGRID-VARS:' . "\n";
    $string .= 'maxEntriesPerPage: ' . $this->getMaxEntriesPerPage() . "\n";
    $string .= 'multiSort: ' . $this->getMultiSort() . "\n";
    $string .= 'sort: ' . $this->getSort() . "\n";
    $string .= 'extended: ' . $this->getExtended() . "\n";
    $string .= "\n";
    $string .= 'DATAGRID-PARAM-NAMES:' . "\n";
    $string .= 'currentPage: ' . $this->getParamNameCurrentPage() . "\n";
    $string .= 'maxEntries: ' . $this->getParamNameMaxEntries() . "\n";
    $string .= 'sortation: ' . $this->getParamNameSortation() . "\n";
    $string .= 'search: ' . $this->getParamNameSearch() . "\n";
    $string .= 'extended: ' . $this->getParamNameExtended() . "\n";
    $string .= "\n";
    $string .= 'DATAGRID-ROUTE:' . "\n";
    $string .= $this->route . "\n";

    return $string;
  }

}
