<?php

namespace HBM\DatagridBundle\Model;

class Datagrid {

	/* SESSION ****************************************************************/

	/**
	 * @var string
	 */
	private $sessionPrefix;

	/**
	 * @var array
	 */
	private $sessionUseFor;

	/* DEFAULTS ***************************************************************/

	/**
	 * @var integer
	 */
	private $maxEntriesPerPage;

	/* PARAM NAMES ************************************************************/

	/**
	 * @var string
	 */
	private $paramNameCurrentPage;

	/**
	 * @var string
	 */
	private $paramsNameMaxEntries;

	/**
	 * @var string
	 */
	private $paramsNameSortation;

	/* QUERY ******************************************************************/

	/**
	 * @var string
	 */
	private $queryEncode;

	/**
	 * @var string
	 */
	private $queryItemSep;

	/**
	 * @var string
	 */
	private $queryValueSep;


	/* CONFIG *****************************************************************/

	/**
	 * @var boolean
	 */
	private $sort;

	/**
	 * @var boolean
	 */
	private $multiSort;

	/* CACHE ******************************************************************/
	
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
	
	/* ROUTING ****************************************************************/

	/**
	 * @var Route
	 */
	private $route;

	/* COLUMNS/ROWS ***********************************************************/

	/**
	 * @var array
	 */
	private $sortations = array();

	/**
	 * @var array
	 */
	private $cells;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 */
	private $results;

	/* PAGINATION/MENU ********************************************************/

	/**
	 * @var Pagination
	 */
	private $pagination;

	/**
	 * @var Menu
	 */
	private $menu;

	/* CONSTRUCTOR ************************************************************/

	public function __construct($config)
	{
		$this->results = new \Doctrine\Common\Collections\ArrayCollection();

		$this->setSort($config['datagrid']['sort']);
		$this->setMultiSort($config['datagrid']['multi_sort']);
		$this->setMaxEntriesPerPage($config['datagrid']['max_entries_per_page']);
		
		$this->setCacheEnabled($config['cache']['enabled']);
		$this->setCacheSeconds($config['cache']['seconds']);
		$this->setCachePrefix($config['cache']['prefix']);

		$this->setParamNameCurrentPage($config['query']['param_names']['current_page']);
		$this->setParamNameMaxEntries($config['query']['param_names']['max_entries']);
		$this->setParamNameSortation($config['query']['param_names']['sortation']);

		$this->setQueryEncode($config['query']['encode']);

		$this->setSessionPrefix($config['session']['prefix']);
		$this->setSessionUseFor($config['session']['use_for']);
	}

	/* GETTER/SETTER **********************************************************/

	public function setSessionPrefix($sessionPrefix) { $this->sessionPrefix = $sessionPrefix; }
	public function getSessionPrefix() { return $this->sessionPrefix; }

	public function setSessionUseFor($sessionUseFor) { $this->sessionUseFor = $sessionUseFor; }
	public function getSessionUseFor() { return $this->sessionUseFor; }


	public function setMaxEntriesPerPage($maxEntriesPerPage) { $this->maxEntriesPerPage = max(1, $maxEntriesPerPage); }
	public function getMaxEntriesPerPage() { return $this->maxEntriesPerPage; }

	
	public function setCacheEnabled($cacheEnabled) { $this->cacheEnabled = $cacheEnabled; }
	public function getCacheEnabled() { return $this->cacheEnabled; }
	
	public function setCacheSeconds($cacheSeconds) { $this->cacheSeconds =$cacheSeconds; }
	public function getCacheSeconds() { return $this->cacheSeconds; }
	
	public function setCachePrefix($cachePrefix) { $this->cachePrefix = $cachePrefix; }
	public function getCachePrefix() { return $this->cachePrefix; }

	
	public function setParamNameCurrentPage($paramNameCurrentPage) { $this->paramNameCurrentPage = $paramNameCurrentPage; }
	public function getParamNameCurrentPage() { return $this->paramNameCurrentPage; }

	public function setParamNameMaxEntries($paramNameMaxEntries) { $this->paramNameMaxEntries = $paramNameMaxEntries; }
	public function getParamNameMaxEntries() { return $this->paramNameMaxEntries; }

	public function setParamNameSortation($paramNameSortation) { $this->paramNameSortation = $paramNameSortation; }
	public function getParamNameSortation() { return $this->paramNameSortation; }


	public function setQueryEncode($queryEncode) { $this->queryEncode = $queryEncode; }
	public function getQueryEncode() { return $this->queryEncode; }


	public function setSort($sort) { $this->sort = $sort; }
	public function getSort() { return $this->sort; }

	public function setMultiSort($multiSort) { $this->multiSort = $multiSort; }
	public function getMultiSort() { return $this->multiSort; }


	public function setRoute(Route $route) { $this->route = $route; }
	public function getRoute() { return $this->route; }


	public function setResults($results) { $this->results = $results; }
	public function getResults() { return $this->results; }

	public function setSortations($sortations) { $this->sortations = $sortations; }
	public function getSortations() { return $this->sortations; }

	public function setCells($cells) { $this->cells = $cells; }
	public function getCells() { return $this->cells; }

	/**
	 * Set pagination
	 *
	 * @param DatagridPagination $pagination
	 * @return Datagrid
	 */
	public function setPagination($pagination)
	{
		$pagination->setDatagrid($this);

		$this->pagination = $pagination;

		return $this;
	}

	/**
	 * Get pagination
	 *
	 * @return DatagridPagination
	 */
	public function getPagination()
	{
		return $this->pagination;
	}

	/**
	 * Set menu
	 *
	 * @param DatagridMenu $menu
	 * @return Datagrid
	 */
	public function setMenu($menu)
	{
		$menu->setDatagrid($this);

		$this->menu = $menu;

		return $this;
	}

	/**
	 * Get menu
	 *
	 * @return DatagridMenu
	 */
	public function getMenu()
	{
		return $this->menu;
	}

	/* CUSTOM *****************************************************************/

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

	public function __toString() {
		$string = '';
		$string .= 'DATAGRID-COLLECTIONS:'."\n";
		$string .= count($this->cells).' Cells'."\n";
		$string .= count($this->results).' Results'."\n";
		$string .= "\n";
		$string .= 'DATAGRID-VARS:'."\n";
		$string .= 'maxEntriesPerPage: '.$this->getMaxEntriesPerPage()."\n";
		$string .= 'multiSort: '.$this->getMultiSort()."\n";
		$string .= "\n";
		$string .= 'DATAGRID-PARAM-NAMES:'."\n";
		$string .= 'currentPage: '.$this->getParamNameCurrentPage()."\n";
		$string .= 'maxEntries: '.$this->getParamNameMaxEntries()."\n";
		$string .= 'sortation: '.$this->getParamNameSortation()."\n";
		$string .= "\n";
		$string .= 'DATAGRID-ROUTE:'."\n";
		$string .= $this->route."\n";

		return $string;
	}

}
