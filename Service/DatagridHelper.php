<?php
namespace HBM\DatagridBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ORM\QueryBuilder;
use HBM\DatagridBundle\Model\Datagrid;
use HBM\DatagridBundle\Model\DatagridMenu;
use HBM\DatagridBundle\Model\DatagridPagination;
use HBM\DatagridBundle\Model\Export;
use HBM\DatagridBundle\Model\ExportCSV;
use HBM\DatagridBundle\Model\ExportJSON;
use HBM\DatagridBundle\Model\ExportXLSX;
use HBM\DatagridBundle\Model\Route;
use HBM\DatagridBundle\Model\RouteLink;
use HBM\DatagridBundle\Model\TableCell;
use HBM\DatagridBundle\Service\QueryBuilderStrategy\Common\QueryBuilderStrategyInterface;
use HBM\DatagridBundle\Service\QueryBuilderStrategy\EntityQueryBuilder;
use HBM\DatagridBundle\Service\QueryBuilderStrategy\MongoDBDocumentQueryBuilder;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

/**
 * Service
 *
 * Makes pagination easy.
 */
class DatagridHelper {

  /**
   * @var array
   */
  private $config;

  /**
   * @var Router
   */
  protected $router;

  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var Session
   */
  private $session;

  /**
   * @var Datagrid
   */
  private $datagrid;

  /**
   * @var QueryBuilderStrategyInterface
   */
  private $queryBuilderStrategy;

  /**
   * @var array
   */
  private $results;

  /**
   * @var integer
   */
  private $resultsNumber;

  /**
   * @var array
   */
  private $exports = [];

  /**
   * DatagridHelper constructor.
   *
   * @param array $config
   * @param Router $router
   * @param LoggerInterface $logger
   */
  public function __construct(array $config, Router $router, LoggerInterface $logger) {
    $this->config = $config;
    $this->router = $router;
    $this->logger = $logger;

    $this->setExport('csv', new ExportCSV());
    $this->setExport('xlsx', new ExportXLSX());
    $this->setExport('json', new ExportJSON());
  }

  public function reset() {
    $this->session = NULL;
    $this->datagrid = NULL;
    $this->qb = NULL;
    $this->results = NULL;
    $this->resultsNumber = NULL;
  }

  public function getConfigValue($scope, $key) {
    if (isset($this->config[$scope][$key])) {
      return $this->config[$scope][$key];
    }

    return NULL;
  }

  public function setExport($identifier, Export $export) {
    $this->exports[$identifier] = $export;
  }

  /**
   * @param $identifier
   * @return Export|null
   */
  public function getExport($identifier) {
    if (isset($this->exports[$identifier])) {
      return $this->exports[$identifier];
    }

    return NULL;
  }

  /**
   * @return Datagrid
   */
  public function getDatagrid() {
    if ($this->datagrid === NULL) {
      $this->datagrid = new Datagrid($this->config);
      $this->datagrid->setMenu(new DatagridMenu($this->config));
      $this->datagrid->setPagination(new DatagridPagination($this->config));
    }

    return $this->datagrid;
  }

  /**
   * Inits a datagrid.
   *
   * @param string $route
   * @param array $defaults
   * @param integer $page
   * @param integer $maxEntries
   * @param string $sortations
   * @param string $searchValues
   * @param integer $extended
   */
  public function initDatagrid($route, array $defaults = [], $page = NULL, $maxEntries = NULL, $sortations = NULL, $searchValues = NULL, $extended = NULL) {
    $this->reset();

    $paramNamePage = $this->getDatagrid()->getParamNameCurrentPage();
    $paramNameMaxEntries = $this->getDatagrid()->getParamNameMaxEntries();
    $paramNameSortation = $this->getDatagrid()->getParamNameSortation();
    $paramNameSearch = $this->getDatagrid()->getParamNameSearch();
    $paramNameExtended = $this->getDatagrid()->getParamNameExtended();

    // Set params
    $paramsOrig = [
      $paramNamePage       => $page,
      $paramNameMaxEntries => $maxEntries,
      $paramNameSortation  => $sortations,
      $paramNameSearch     => $searchValues,
      $paramNameExtended   => $extended,
    ];

    $paramsHandled = [];

    $key = $paramNamePage;
    if (array_key_exists($key, $defaults)) {
      $fallback = $defaults[$key]?:'1';
      $paramsHandled[$key] = $this->handleParam($paramsOrig, $key, $fallback);
      $this->getDatagrid()->getPagination()->setPageCurrent($paramsHandled[$key]);
    }

    $key = $paramNameMaxEntries;
    if (array_key_exists($key, $defaults)) {
      $fallback = $defaults[$key]?:$this->getDatagrid()->getMaxEntriesPerPage();
      $paramsHandled[$key] = $this->handleParam($paramsOrig, $key, $fallback);
      $this->getDatagrid()->setMaxEntriesPerPage($paramsHandled[$key]);
    }

    $key = $paramNameSortation;
    if (array_key_exists($key, $defaults)) {
      $fallback = $defaults[$key]?:'{}';
      $paramsHandled[$key] = $this->handleParam($paramsOrig, $key, $fallback);
      $this->getDatagrid()->setSortations($this->getQueryParams($paramsHandled[$key]));
    }

    $key = $paramNameSearch;
    if (array_key_exists($key, $defaults)) {
      $fallback = $defaults[$key]?:'{}';
      $paramsHandled[$key] = $this->handleParam($paramsOrig, $key, $fallback);
      $this->getDatagrid()->getMenu()->setSearchValues($this->getQueryParams($paramsHandled[$key]));
    }

    $key = $paramNameExtended;
    if (array_key_exists($key, $defaults)) {
      $fallback = $defaults[$key]?:'0';
      $paramsHandled[$key] = $this->handleParam($paramsOrig, $key, $fallback);
      $this->getDatagrid()->setExtended($paramsHandled[$key]);
    }

    // Make sure to include route specific params.
    $paramsHandled = array_merge($defaults, $paramsHandled);

    // Set routes
    $routeObj = new Route($route, $paramsHandled);

    $this->getDatagrid()->setRoute($routeObj);
    $this->getDatagrid()->getMenu()->setRoute($routeObj);
    $this->getDatagrid()->getPagination()->setRoute($routeObj);

    // Set route search
    if (array_key_exists($paramNameSearch, $defaults)) {
      $this->getDatagrid()->getMenu()->setRouteSearch($routeObj);
    }

    // Set route reset
    $paramsToUse = $paramsHandled;
    foreach ($this->getDatagrid()->getSessionUseFor() as $key) {
      if (array_key_exists($key, $paramsToUse)) {
        $paramsToUse[$key] = '-1';
      }
    }
    if (array_key_exists($paramNameSearch, $paramsToUse)) {
      $paramsToUse[$paramNameSearch] = '{}';
    }
    $this->getDatagrid()->getMenu()->setRouteReset(new Route($route, $paramsToUse));

    // Set route extended
    if (array_key_exists($paramNameExtended, $defaults)) {
      $paramsToUse = $paramsHandled;
      $paramsToUse[$paramNameExtended] = $paramsToUse[$paramNameExtended]=='1'?'0':'1';
      $this->getDatagrid()->getMenu()->setRouteExtended(new Route($route, $paramsToUse));
    }
  }

  /**
   * @param Route $route
   * @param $params
   *
   * @return Datagrid
   */
  public function createSimpleDatagrid(Route $route, $params) {
    // DEFAULTS
    $num = $this->getDatagrid()->getMaxEntriesPerPage();
    if (isset($params[$this->getDatagrid()->getParamNameMaxEntries()])) {
      $num = $params[$this->getDatagrid()->getParamNameMaxEntries()];
    }
    $page = $this->getDatagrid()->getPagination()->getPageCurrent();
    if (isset($params[$this->getDatagrid()->getParamNameCurrentPage()])) {
      $page = $params[$this->getDatagrid()->getParamNameCurrentPage()];
    }
    $this->getDatagrid()->setRoute($route);
    $this->getDatagrid()->setMaxEntriesPerPage($num);

    // MENU
    $this->getDatagrid()->setSort(FALSE);
    $this->getDatagrid()->setExtended(FALSE);
    $this->getDatagrid()->getMenu()->setShow(FALSE);

    // PAGINATON
    $this->getDatagrid()->getPagination()->setRoute($route);
    $this->getDatagrid()->getPagination()->setPageCurrent($page);

    return $this->getDatagrid();
  }

  /**
   * @param $sort_string
   *
   * @return array|mixed|null
   */
  public function setSortations($sort_string) {
    $sortations = $this->getQueryParams($sort_string);

    $this->getDatagrid()->setSortations($sortations);

    return $sortations;
  }

  /**
   * @return array
   */
  public function getSortations() {
    return $this->getDatagrid()->getSortations();
  }

  /**
   * @param $search_string
   *
   * @return array|mixed|null
   */
  public function setSearchValues($search_string) {
    $searchValues = $this->getQueryParams($search_string);

    $this->getDatagrid()->getMenu()->setSearchValues($searchValues);

    return $searchValues;
  }

  /**
   * @return array
   */
  public function getSearchValues() {
    return $this->getDatagrid()->getMenu()->getSearchValues();
  }

  /**
   * @param Route $route
   */
  public function setDefaultRoute(Route $route) {
    $this->getDatagrid()->setRoute($route);
    $this->getDatagrid()->getMenu()->setRoute($route);
    $this->getDatagrid()->getMenu()->setRouteReset($route);
    $this->getDatagrid()->getMenu()->setRouteExtended($route);
    $this->getDatagrid()->getMenu()->setRouteSearch($route);
    $this->getDatagrid()->getPagination()->setRoute($route);
  }

  /**
   * @param QueryBuilder|null $qb
   * @param string $distinctFieldName
   */
  public function setQueryBuilderEntity(?QueryBuilder $qb, string $distinctFieldName = 'id') : void {
    $this->queryBuilderStrategy = new EntityQueryBuilder();
    $this->queryBuilderStrategy->setDistinctFieldName($distinctFieldName);
    $this->queryBuilderStrategy->setDatagrid($this->getDatagrid());
    $this->queryBuilderStrategy->setQueryBuilder($qb);
  }

  /**
   * @param Builder|null $qb
   * @param DocumentManager $dm
   * @param string $distinctFieldName
   */
  public function setQueryBuilderMongoDBDocument(?Builder $qb, DocumentManager $dm, string $distinctFieldName = 'id') : void {
    $this->queryBuilderStrategy = new MongoDBDocumentQueryBuilder();
    $this->queryBuilderStrategy->setDistinctFieldName($distinctFieldName);
    $this->queryBuilderStrategy->setDatagrid($this->getDatagrid());
    $this->queryBuilderStrategy->setQueryBuilder($qb);
    $this->queryBuilderStrategy->setDocumentManager($dm);
  }


  /**
   * @param Request $request
   * @param $searchFields
   * @param array $defaults
   *
   * @return bool|RedirectResponse
   */
  public function handleSearch(Request $request, $searchFields, $defaults = []) {
    if ($request->isMethod('post') && !$request->request->has('export-type')) {
      $params = $this->handleSearchParams($request, $searchFields);
      $url = $this->router->generate($this->getDatagrid()->getRoute()->getName(), array_merge($defaults, $params));
      return new RedirectResponse($url);
    }

    return FALSE;
  }

  /**
   * @param Request $request
   * @param $searchFields
   *
   * @return array
   */
  public function handleSearchParams(Request $request, $searchFields) {
    $searchParams = [];
    foreach ($searchFields as $key => $value) {
      $searchParams[$key] = $request->request->get($key, '');
      if (isset($value['options'])) {
        $searchParams[$key.'-options'] = $request->request->get($key.'-options', []);
      }
    }

    $sortParams = $this->getDatagrid()->getSortations();

    return [
      $this->getDatagrid()->getParamNameMaxEntries() => $this->getDatagrid()->getMaxEntriesPerPage(),
      $this->getDatagrid()->getParamNameCurrentPage() => 1,
      $this->getDatagrid()->getParamNameSortation() => $this->getQueryString($sortParams),
      $this->getDatagrid()->getParamNameSearch() => $this->getQueryString($searchParams),
      $this->getDatagrid()->getParamNameExtended() => $this->getDatagrid()->getExtended()
    ];
  }

  /**
   * @param Request $request
   * @param $name
   * @param FlashBagInterface|NULL $flashBag
   *
   * @return bool|RedirectResponse
   */
  public function handleExport(Request $request, $name, FlashBagInterface $flashBag = NULL) {
    if ($request->isMethod('post') && $request->request->has('export-type')) {
      // Not allowed.
      if (!$this->getDatagrid()->getMenu()->getShowExport()) {
        if ($flashBag) {
          $flashBag->add('error', 'Der Export ist deaktiviert!');
        }
        $url = $this->router->generate($this->getDatagrid()->getRoute()->getName(), $this->getDatagrid()->getRoute()->getDefaults());
        return new RedirectResponse($url);
      }

      // Set resources.
      foreach ($this->getDatagrid()->getMenu()->getExportsResources() as $key => $value) {
        ini_set($key, $value);
      }

      // Do export.
      if ($export = $this->getExport($request->request->get('export-type'))) {
        $export->init();
        $export->setName($name);
        $export = $this->runExport($export);
        $export->finish();
        return $export->output();
      }

      // Export failed.
      if ($flashBag) {
        $flashBag->add('error', 'Der Export leider fehlgeschlagen!');
      }
      $url = $this->router->generate($this->getDatagrid()->getRoute()->getName(), $this->getDatagrid()->getRoute()->getDefaults());
      return new RedirectResponse($url);
    }

    return FALSE;
  }

  /**
   * @param Export $export
   *
   * @return Export
   */
  public function runExport(Export $export) : Export {
    $export->setCells($this->getDatagrid()->getCells());
    $export->addHeader();

    if ($this->results) {
      foreach ($this->results as $obj) {
        $export->addRow($obj);
      }
      return $export;
    }

    if ($this->queryBuilderStrategy) {
      return $this->queryBuilderStrategy->doExport($export);
    }

    return $export;
  }

  /**
   * Set a fixed set of results.
   *
   * @param $results
   */
  public function setResults($results) {
    $this->results = $results;
    $this->resultsNumber = count($results);
  }

  /**
   * Make sure to set this after setting the results.
   *
   * @param $resultsNumber
   */
  public function setResultsNumber($resultsNumber) {
    $this->resultsNumber = $resultsNumber;
  }

  /**
   * @param Session $session
   * @param null $additionalPrefix
   */
  public function setSession(Session $session, $additionalPrefix = NULL) {
    $this->session = $session;
    if ($additionalPrefix !== NULL) {
      $this->getDatagrid()->setSessionPrefix($this->getDatagrid()->getSessionPrefix().$additionalPrefix);
    }
  }

  /**
   * @param $params
   *
   * @return mixed
   */
  public function handleParams($params) {
    // Sortation
    $key = $this->getDatagrid()->getParamNameCurrentPage();
    $default = '1';
    $params[$key] = $this->handleParam($params, $key, $default);

    // Max entries
    $key = $this->getDatagrid()->getParamNameMaxEntries();
    $default = $this->getDatagrid()->getMaxEntriesPerPage();
    $params[$key] = $this->handleParam($params, $key, $default);

    // Sortations
    $key = $this->getDatagrid()->getParamNameSortation();
    $default = NULL;
    $params[$key] = $this->handleParam($params, $key, $default);

    // Search values
    $key = $this->getDatagrid()->getParamNameSearch();
    $default = NULL;
    $params[$key] = $this->handleParam($params, $key, $default);

    // Extended
    $key = $this->getDatagrid()->getParamNameExtended();
    $default = '0';
    $params[$key] = $this->handleParam($params, $key, $default);

    return $params;
  }

  private function handleParam($params, $key, $default = NULL) {
    $prefix = $this->getDatagrid()->getSessionPrefix();
    $use_for = $this->getDatagrid()->getSessionUseFor();

    if (array_key_exists($key, $params)) {
      // Set default value
      if ($params[$key] === '-1') {
        $params[$key] = $default;
      }

      // Load from session
      if ($params[$key] === NULL) {
        if ($this->session && in_array($key, $use_for, TRUE)) {
          $params[$key] = $this->session->get($prefix.$key, $default);
        }
      }

      if (!$params[$key]) {
        $params[$key] = $default;
      }

      // Save to session
      if ($this->session && in_array($key, $use_for)) {
        $this->session->set($prefix.$key, $params[$key]);
      }

      return $params[$key];
    }

    return $default;
  }

  /**
   * @param $var
   *
   * @return array|mixed|null
   */
  public function getQueryParams($var) {
    if ($this->getDatagrid()->getQueryEncode() === 'json') {
      $queryParams = json_decode($var, TRUE);
      if ($queryParams === NULL) {
        $queryParams = [];
      } else {
        foreach ($queryParams as $key => $value) {
          if (is_array($value)) {
            $queryParams[$key] = [];
            foreach ($value as $valueKey => $valueValue) {
              $queryParams[$key][$valueKey] = urldecode($valueValue);
            }
          } else {
            $queryParams[$key] = urldecode($value);
          }
        }
      }
    } else {
      throw new InvalidArgumentException('No other query encoding implemented yet!');
    }

    return $queryParams;
  }

  /**
   * @param $vars
   *
   * @return false|string
   *
   * @throws \JsonException
   */
  public function getQueryString($vars) {
    if ($this->getDatagrid()->getQueryEncode() === 'json') {
      foreach ($vars as $key => $value) {
        if (is_array($value)) {
          $vars[$key] = [];
          foreach ($value as $valueKey => $valueValue) {
            $vars[$key][$valueKey] = urlencode($valueValue);
          }
        } else {
          $vars[$key] = urlencode($value);
        }
      }
      $queryString = json_encode($vars, JSON_THROW_ON_ERROR);
    } else {
      throw new InvalidArgumentException('No other query decoding implemented yet!');
    }

    return $queryString;
  }

  /**
   * @return int|null
   */
  public function getNumber() : ?int {
    if ($this->resultsNumber !== NULL) {
      return $this->resultsNumber;
    }

    if ($this->queryBuilderStrategy) {
      return $this->queryBuilderStrategy->count();
    }

    return NULL;
  }

  /**
   * @return array|ArrayCollection|mixed
   */
  public function getResults() {
    if ($this->results !== NULL) {
      return $this->results;
    }

    if ($this->queryBuilderStrategy) {
      return $this->queryBuilderStrategy->getResults();
    }

    return new ArrayCollection();
  }

  /**
   * Returns the calculated paginated datagrid.
   *
   * @return Datagrid
   */
  public function paginate() {
    $datagrid = $this->getDatagrid();
    $pagination = $datagrid->getPagination();
    $menu = $datagrid->getMenu();

    $max_entries = $datagrid->getMaxEntriesPerPage();
    $max_links = $pagination->getMaxLinksPerPage();

    // Query number
    $number = $this->getNumber();


    $pagination = $this->getDatagrid()->getPagination();

    // Calculate basics
    $pagination->setNumberTotal($number);
    $pagination->setPageMax(ceil($pagination->getNumberTotal() / $max_entries));
    $pagination->setPageCurrent(max([
      1,
      min(array($pagination->getPageCurrent(), $pagination->getPageMax()))
    ]));

    $pagination->setOffset(max([
      0,
      ($pagination->getPageCurrent() - 1) * $max_entries
    ]));

    // Calculate numbers
    $pagination->setNumberFrom(0);
    $pagination->setNumberThru(0);
    if ($pagination->getNumberTotal() > 0) {
      $pagination->setNumberFrom(max([
        1,
        ($pagination->getPageCurrent() - 1) * $max_entries + 1
      ]));
      $pagination->setNumberThru(min([
        $pagination->getNumberTotal(),
        $pagination->getNumberFrom() + $max_entries - 1
      ]));
    }

    // Calculate pages
    $pagination->setPageFrom(max([
      1,
      $pagination->getPageCurrent() - floor(($max_links - 1) / 2)
    ]));
    $pagination->setPageThru(min([
      $pagination->getPageMax(),
      $pagination->getPageFrom() + $max_links - 1
    ]));
    if ($pagination->getPageThru() === $pagination->getPageMax()) {
      $pagination->setPageFrom(max([
        1,
        $pagination->getPageThru() - $max_links
      ]));
    }

    // Calculate links (pagination)
    $this->handlePaginationLinks();

    // Calculate links (menu)
    if ($menu->getShow() && $menu->getShowMaxEntriesSelection()) {
      foreach ($menu->getMaxEntriesSelection() as $value) {
        $menu->addLink($menu->createLink($value));
      }
    }

    // Calculate links (datagrid)
    $this->handleSortationLinks();


    // Query results
    $results = $this->getResults();

    $pagination->setNumber(count($results));
    $datagrid->setResults($results);

    $this->logger->debug($datagrid);
    $this->logger->debug($pagination);
    $this->logger->debug($menu);

    return $datagrid;
  }

  private function handlePaginationLinks() {
    $pagination = $this->getDatagrid()->getPagination();

    if ($pagination->getPageCurrent() !== 1) {
      $pagination->setLinkFirst($pagination->createLink(1));
    }
    if ($pagination->getPageCurrent() !== $pagination->getPageMax()) {
      $pagination->setLinkLast($pagination->createLink($pagination->getPageMax()));
    }

    if ($pagination->getPageCurrent() > 1) {
      $pagination->setLinkPrev($pagination->createLink($pagination->getPageCurrent() - 1));
    }
    if ($pagination->getPageCurrent() < $pagination->getPageMax()) {
      $pagination->setLinkNext($pagination->createLink($pagination->getPageCurrent() + 1));
    }
    for ($i = $pagination->getPageFrom(); $i <= $pagination->getPageThru(); $i++) {
      $pagination->addLink($pagination->createLink($i));
    }
  }

  private function handleSortationLinks() {
    $datagrid = $this->getDatagrid();

    if ($datagrid->getSort()) {
      $sortations = $datagrid->getSortations();
      foreach ($datagrid->getCells() as $cell) {
        /** @var TableCell $cell */
        if ($cell->isSortable()) {
          foreach ($cell->getSortKeys() as $sortKeyKey => $sortKeyValue) {
            $new_sortations = $sortations;
            if (!$datagrid->getMultiSort()) {
              $new_sortations = [];
            }

            if (isset($sortations[$sortKeyKey]) && ($sortations[$sortKeyKey] === 'asc')) {
              // If asc is set, offer desc as next direction
              $new_sortations[$sortKeyKey] = 'desc';
            } elseif (isset($sortations[$sortKeyKey]) && ($sortations[$sortKeyKey] === 'desc')) {
              // If desc is set, unset direction
              unset($new_sortations[$sortKeyKey]);
            } else {
              // If nothing is set, offer as as next direction
              $new_sortations[$sortKeyKey] = 'asc';
            }

            $params = [$datagrid->getParamNameSortation() => $this->getQueryString($new_sortations)];
            $cell->addTheadLink($sortKeyKey, new RouteLink($params, $datagrid->getRoute()));
          }
        }
      }
    }
  }

}
