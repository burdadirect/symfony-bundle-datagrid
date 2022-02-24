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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Service
 *
 * Makes pagination easy.
 */
class DatagridHelper {

  public const EXPORT_CSV  = 'csv';
  public const EXPORT_JSON = 'json';
  public const EXPORT_XLSX = 'xlsx';

  private array $config;

  private QueryEncoder $queryEncoder;

  private RouterInterface $router;

  private LoggerInterface $logger;

  private ?SessionInterface $session;

  private ?Datagrid $datagrid = null;

  private ?QueryBuilderStrategyInterface $queryBuilderStrategy = null;

  private ?array $results = null;

  private ?int $resultsNumber = null;

  private array $exports = [];

  /**
   * DatagridHelper constructor.
   *
   * @param array $config
   * @param QueryEncoder $queryEncoder
   * @param RouterInterface $router
   * @param LoggerInterface $logger
   */
  public function __construct(array $config, QueryEncoder $queryEncoder, RouterInterface $router, LoggerInterface $logger) {
    $this->config = $config;
    $this->queryEncoder = $queryEncoder;
    $this->router = $router;
    $this->logger = $logger;

    $this->setExport(self::EXPORT_CSV, new ExportCSV());
    $this->setExport(self::EXPORT_JSON, new ExportJSON());
    $this->setExport(self::EXPORT_XLSX, new ExportXLSX());
  }

  public function reset(): void {
    $this->session = NULL;
    $this->datagrid = NULL;
    $this->results = NULL;
    $this->resultsNumber = NULL;
  }

  public function getConfigValue($scope, $key) {
    return $this->config[$scope][$key] ?? NULL;
  }

  public function setExport($identifier, Export $export): void {
    $this->exports[$identifier] = $export;
  }

  /**
   * @param $identifier
   * @return Export|null
   */
  public function getExport($identifier): ?Export {
    return $this->exports[$identifier] ?? NULL;
  }

  /**
   * @return Datagrid
   */
  public function getDatagrid(): Datagrid {
    if ($this->datagrid === NULL) {
      $this->datagrid = new Datagrid($this->config);
      $this->datagrid->setMenu(new DatagridMenu($this->config));
      $this->datagrid->setPagination(new DatagridPagination($this->config));
    }

    return $this->datagrid;
  }

  /**
   * @return Datagrid
   */
  private function dg(): Datagrid {
    return $this->getDatagrid();
  }

  /**
   * Inits a datagrid.
   *
   * @param string $route
   * @param array $defaults
   * @param int|null $page
   * @param int|null $maxEntries
   * @param string|null $sortations
   * @param string|null $searchValues
   * @param string|int|bool|null $extended
   */
  public function initDatagrid(string $route, array $defaults = [], ?int $page = NULL, ?int $maxEntries = NULL, ?string $sortations = NULL, ?string $searchValues = NULL, $extended = NULL): void {
    $this->reset();

    $paramNamePage = $this->dg()->getParamNameCurrentPage();
    $paramNameMaxEntries = $this->dg()->getParamNameMaxEntries();
    $paramNameSortation = $this->dg()->getParamNameSortation();
    $paramNameSearch = $this->dg()->getParamNameSearch();
    $paramNameExtended = $this->dg()->getParamNameExtended();

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
      $fallback = $defaults[$key] ?: '1';
      $paramsHandled[$key] = $this->handleParam($paramsOrig, $key, $fallback);
      $this->dg()->getPagination()->setPageCurrent($paramsHandled[$key]);
    }

    $key = $paramNameMaxEntries;
    if (array_key_exists($key, $defaults)) {
      $fallback = $defaults[$key] ?: $this->dg()->getMaxEntriesPerPage();
      $paramsHandled[$key] = $this->handleParam($paramsOrig, $key, $fallback);
      $this->dg()->setMaxEntriesPerPage($paramsHandled[$key]);
    }

    $key = $paramNameSortation;
    if (array_key_exists($key, $defaults)) {
      $fallback = $defaults[$key] ?: '{}';
      $paramsHandled[$key] = $this->handleParam($paramsOrig, $key, $fallback);
      $this->dg()->setSortations($this->getQueryParams($paramsHandled[$key]));
    }

    $key = $paramNameSearch;
    if (array_key_exists($key, $defaults)) {
      $fallback = $defaults[$key] ?: '{}';
      $paramsHandled[$key] = $this->handleParam($paramsOrig, $key, $fallback);
      $this->dg()->getMenu()->setSearchValues($this->getQueryParams($paramsHandled[$key]));
    }

    $key = $paramNameExtended;
    if (array_key_exists($key, $defaults)) {
      $fallback = $defaults[$key] ?: '0';
      $paramsHandled[$key] = $this->handleParam($paramsOrig, $key, $fallback);
      $this->dg()->setExtended($paramsHandled[$key]);
    }

    // Make sure to include route specific params.
    $paramsHandled = array_merge($defaults, $paramsHandled);

    // Set routes
    $routeObj = new Route($route, $paramsHandled);

    $this->dg()->setRoute($routeObj);
    $this->dg()->getMenu()->setRoute($routeObj);
    $this->dg()->getPagination()->setRoute($routeObj);

    // Set route search
    if (array_key_exists($paramNameSearch, $defaults)) {
      $this->dg()->getMenu()->setRouteSearch($routeObj);
    }

    // Set route reset
    $paramsToUse = $paramsHandled;
    foreach ($this->dg()->getSessionUseFor() as $key) {
      if (array_key_exists($key, $paramsToUse)) {
        $paramsToUse[$key] = '-1';
      }
    }
    if (array_key_exists($paramNameSearch, $paramsToUse)) {
      $paramsToUse[$paramNameSearch] = '{}';
    }
    $this->dg()->getMenu()->setRouteReset(new Route($route, $paramsToUse));

    // Set route extended
    if (array_key_exists($paramNameExtended, $defaults)) {
      $paramsToUse = $paramsHandled;
      $paramsToUse[$paramNameExtended] = !(bool)$paramsToUse[$paramNameExtended];
      $this->dg()->getMenu()->setRouteExtended(new Route($route, $paramsToUse));
    }
  }

  /**
   * @param Route $route
   * @param $params
   *
   * @return Datagrid
   */
  public function createSimpleDatagrid(Route $route, $params): Datagrid {
    // DEFAULTS
    $num = $params[$this->dg()->getParamNameMaxEntries()] ?? $this->dg()->getMaxEntriesPerPage();
    $page = $params[$this->dg()->getParamNameCurrentPage()] ?? $this->dg()->getPagination()->getPageCurrent();

    $this->dg()->setRoute($route);
    $this->dg()->setMaxEntriesPerPage($num);

    // MENU
    $this->dg()->setSort(FALSE);
    $this->dg()->setExtended(FALSE);
    $this->dg()->getMenu()->setShow(FALSE);

    // PAGINATON
    $this->dg()->getPagination()->setRoute($route);
    $this->dg()->getPagination()->setPageCurrent($page);

    return $this->dg();
  }

  /**
   * @param string|null $sortString
   *
   * @return array|mixed|null
   */
  public function setSortations(?string $sortString) {
    $sortations = $this->getQueryParams($sortString);

    $this->dg()->setSortations($sortations);

    return $sortations;
  }

  /**
   * @return array
   */
  public function getSortations(): array {
    return $this->dg()->getSortations();
  }

  /**
   * @param string|null $searchString
   *
   * @return array|mixed|null
   */
  public function setSearchValues(?string $searchString) {
    $searchValues = $this->getQueryParams($searchString);

    $this->dg()->getMenu()->setSearchValues($searchValues);

    return $searchValues;
  }

  /**
   * @return array
   */
  public function getSearchValues(): array {
    return $this->dg()->getMenu()->getSearchValues();
  }

  /**
   * @param Route $route
   */
  public function setDefaultRoute(Route $route): void {
    $this->dg()->setRoute($route);
    $this->dg()->getMenu()->setRoute($route);
    $this->dg()->getMenu()->setRouteReset($route);
    $this->dg()->getMenu()->setRouteExtended($route);
    $this->dg()->getMenu()->setRouteSearch($route);
    $this->dg()->getPagination()->setRoute($route);
  }

  /**
   * @param QueryBuilder|null $qb
   * @param string $distinctFieldName
   */
  public function setQueryBuilderEntity(?QueryBuilder $qb, string $distinctFieldName = 'id'): void {
    $this->queryBuilderStrategy = new EntityQueryBuilder();
    $this->queryBuilderStrategy->setDistinctFieldName($distinctFieldName);
    $this->queryBuilderStrategy->setDatagrid($this->dg());
    $this->queryBuilderStrategy->setQueryBuilder($qb);
  }

  /**
   * @param Builder|null $qb
   * @param DocumentManager $dm
   * @param string $distinctFieldName
   */
  public function setQueryBuilderMongoDBDocument(?Builder $qb, DocumentManager $dm, string $distinctFieldName = 'id'): void {
    $this->queryBuilderStrategy = new MongoDBDocumentQueryBuilder();
    $this->queryBuilderStrategy->setDistinctFieldName($distinctFieldName);
    $this->queryBuilderStrategy->setDatagrid($this->dg());
    $this->queryBuilderStrategy->setQueryBuilder($qb);
    $this->queryBuilderStrategy->setDocumentManager($dm);
  }

  /**
   * @param Request $request
   * @param array $searchFields
   * @param array $defaults
   *
   * @return RedirectResponse|null
   */
  public function handleSearch(Request $request, array $searchFields, array $defaults = []): ?RedirectResponse {
    if ($request->isMethod('post') && !$request->request->has('export-type')) {
      $params = $this->handleSearchParams($request, $searchFields);
      $url = $this->router->generate($this->dg()->getRoute()->getName(), array_merge($defaults, $params));
      return new RedirectResponse($url);
    }

    return NULL;
  }

  /**
   * @param Request $request
   * @param array $searchFields
   *
   * @return array
   */
  public function handleSearchParams(Request $request, array $searchFields): array {
    $searchParams = [];
    foreach ($searchFields as $key => $value) {
      $searchParams[$key] = $request->request->get($key, '');
      if (isset($value['options'])) {
        $searchParams[$key.'-options'] = $request->request->get($key.'-options', []);
      }
    }

    $sortParams = $this->dg()->getSortations();

    return [
      $this->dg()->getParamNameMaxEntries() => $this->dg()->getMaxEntriesPerPage(),
      $this->dg()->getParamNameCurrentPage() => 1,
      $this->dg()->getParamNameSortation() => $this->getQueryString($sortParams),
      $this->dg()->getParamNameSearch() => $this->getQueryString($searchParams),
      $this->dg()->getParamNameExtended() => $this->dg()->getExtended()
    ];
  }

  /**
   * @param Request $request
   * @param $name
   * @param FlashBagInterface|NULL $flashBag
   *
   * @return RedirectResponse|Response|null
   */
  public function handleExport(Request $request, $name, FlashBagInterface $flashBag = NULL): ?Response {
    if ($request->isMethod('post') && $request->request->has('export-type')) {
      // Not allowed.
      if (!$this->dg()->getMenu()->getShowExport()) {
        if ($flashBag) {
          $flashBag->add('error', 'Der Export ist deaktiviert!');
        }
        $url = $this->router->generate($this->dg()->getRoute()->getName(), $this->dg()->getRoute()->getDefaults());
        return new RedirectResponse($url);
      }

      // Set resources.
      foreach ($this->dg()->getMenu()->getExportsResources() as $key => $value) {
        ini_set($key, $value);
      }

      // Do export.
      if ($export = $this->getExport($request->request->get('export-type'))) {
        $export = $this->runExport($export, $name);

        return $export->response();
      }

      // Export failed.
      if ($flashBag) {
        $flashBag->add('error', 'Der Export leider fehlgeschlagen!');
      }
      $url = $this->router->generate($this->dg()->getRoute()->getName(), $this->dg()->getRoute()->getDefaults());
      return new RedirectResponse($url);
    }

    return NULL;
  }

  /**
   * @param Export $export
   * @param string|null $name
   *
   * @return Export
   */
  public function runExport(Export $export, ?string $name = null): Export {
    $export->init();
    if ($name !== null) {
      $export->setName($name);
    }

    $export->setCells($this->dg()->getCells());
    $export->addHeader();

    if ($this->results) {
      foreach ($this->results as $obj) {
        $export->addRow($obj);
      }
    } elseif ($this->queryBuilderStrategy) {
      $export = $this->queryBuilderStrategy->doExport($export);
    }

    $export->finish();

    return $export;
  }

  /**
   * @param string $exportType
   * @param string|null $folder
   * @param string|null $name
   *
   * @return string|null
   */
  public function dumpExport(string $exportType, ?string $folder = null, ?string $name = null): ?string {
    if ($export = $this->getExport($exportType)) {
      $export = $this->runExport($export, $name);
      return $export->dump($folder, $name);
    }

    return null;
  }

  /**
   * @param string $exportType
   *
   * @return null
   */
  public function streamExport(string $exportType) {
    if ($export = $this->getExport($exportType)) {
      $export = $this->runExport($export, '');
      return $export->stream();
    }

    return null;
  }

  /**
   * Set a fixed set of results.
   *
   * @param $results
   */
  public function setResults($results): void {
    $this->results = $results;
    $this->resultsNumber = count($results);
  }

  /**
   * Make sure to set this after setting the results.
   *
   * @param int|null $resultsNumber
   */
  public function setResultsNumber(?int $resultsNumber): void {
    $this->resultsNumber = $resultsNumber;
  }

  /**
   * @param SessionInterface $session
   * @param string|null $additionalPrefix
   */
  public function setSession(SessionInterface $session, ?string $additionalPrefix = NULL): void {
    $this->session = $session;
    if ($additionalPrefix !== NULL) {
      $this->dg()->setSessionPrefix($this->dg()->getSessionPrefix().$additionalPrefix);
    }
  }

  /**
   * @param $params
   *
   * @return mixed
   */
  public function handleParams($params) {
    // Sortation
    $key = $this->dg()->getParamNameCurrentPage();
    $default = '1';
    $params[$key] = $this->handleParam($params, $key, $default);

    // Max entries
    $key = $this->dg()->getParamNameMaxEntries();
    $default = $this->dg()->getMaxEntriesPerPage();
    $params[$key] = $this->handleParam($params, $key, $default);

    // Sortations
    $key = $this->dg()->getParamNameSortation();
    $default = NULL;
    $params[$key] = $this->handleParam($params, $key, $default);

    // Search values
    $key = $this->dg()->getParamNameSearch();
    $default = NULL;
    $params[$key] = $this->handleParam($params, $key, $default);

    // Extended
    $key = $this->dg()->getParamNameExtended();
    $default = '0';
    $params[$key] = $this->handleParam($params, $key, $default);

    return $params;
  }

  private function handleParam($params, $key, $default = NULL) {
    $prefix = $this->dg()->getSessionPrefix();
    $use_for = $this->dg()->getSessionUseFor();

    if (array_key_exists($key, $params)) {
      // Set default value
      if (''.$params[$key] === '-1') {
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
      if ($this->session && in_array($key, $use_for, TRUE)) {
        $this->session->set($prefix.$key, $params[$key]);
      }

      return $params[$key];
    }

    return $default;
  }

  /**
   * @return int|null
   */
  public function getNumber(): ?int {
    if ($this->resultsNumber !== NULL) {
      return $this->resultsNumber;
    }

    if ($this->queryBuilderStrategy) {
      return $this->queryBuilderStrategy->count();
    }

    return NULL;
  }

  /**
   * @return array|ArrayCollection
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
  public function paginate(): Datagrid {
    $datagrid = $this->dg();
    $pagination = $datagrid->getPagination();
    $menu = $datagrid->getMenu();

    $max_entries = $datagrid->getMaxEntriesPerPage();
    $max_links = $pagination->getMaxLinksPerPage();

    // Query number
    $number = $this->getNumber();


    $pagination = $this->dg()->getPagination();

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

  private function handlePaginationLinks(): void {
    $pagination = $this->dg()->getPagination();

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

  private function handleSortationLinks(): void {
    $datagrid = $this->dg();

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

  /****************************************************************************/

  private function getQueryString(array $var) {
    return $this->queryEncoder->getQueryString($var, $this->dg()->getQueryEncode());
  }

  private function getQueryParams(?string $var) {
    return $this->queryEncoder->getQueryParams($var, $this->dg()->getQueryEncode());
  }

}
