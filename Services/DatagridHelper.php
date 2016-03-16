<?php
namespace HBM\DatagridBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use HBM\DatagridBundle\Model\TableCell;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use HBM\DatagridBundle\Model\Datagrid;
use HBM\DatagridBundle\Model\DatagridMenu;
use HBM\DatagridBundle\Model\DatagridPagination;
use HBM\DatagridBundle\Model\RouteLink;
use HBM\DatagridBundle\Model\Route;

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
   * @var QueryBuilder
   */
  private $qb;

  /**
   * @var array
   */
  private $results;

  public function __construct($config, $logger) {
    $this->config = $config;
    $this->logger = $logger;
  }

  public function getConfigValue($scope, $key) {
    if (isset($this->config[$scope][$key])) {
      return $this->config[$scope][$key];
    }

    return NULL;
  }

  /**
   * @return \HBM\DatagridBundle\Model\Datagrid
   */
  public function getDatagrid() {
    if ($this->datagrid === NULL) {
      $this->datagrid = new Datagrid($this->config);
      $this->datagrid->setMenu(new DatagridMenu($this->config));
      $this->datagrid->setPagination(new DatagridPagination($this->config));
    }

    return $this->datagrid;
  }

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

  public function setSortations($sort_string) {
    $sortations = $this->getQueryParams($sort_string);

    $this->getDatagrid()->setSortations($sortations);

    return $sortations;
  }

  public function setSearchValues($search_string) {
    $searchValues = $this->getQueryParams($search_string);

    $this->getDatagrid()->getMenu()->setSearchValues($searchValues);

    return $searchValues;
  }

  public function setDefaultRoute(Route $route) {
    $this->getDatagrid()->setRoute($route);
    $this->getDatagrid()->getMenu()->setRoute($route);
    $this->getDatagrid()->getMenu()->setRouteReset($route);
    $this->getDatagrid()->getMenu()->setRouteExtended($route);
    $this->getDatagrid()->getMenu()->setRouteSearch($route);
    $this->getDatagrid()->getPagination()->setRoute($route);
  }

  public function setQueryBuilder(QueryBuilder $qb) {
    $this->qb = $qb;
  }

  public function setResults($results) {
    $this->results = $results;
  }

  public function setSession(Session $session) {
    $this->session = $session;
  }

  public function handleParams($params) {
    // Max entries
    $key = $this->getDatagrid()->getParamNameMaxEntries();
    $default = $this->getDatagrid()->getMaxEntriesPerPage();

    $params[$key] = $this->handleParam($params, $key, $default);

    // Sortation
    $key = $this->getDatagrid()->getParamNameSortation();
    $default = NULL;

    $params[$key] = $this->handleParam($params, $key, $default);

    return $params;
  }

  private function handleParam($params, $key, $default = NULL) {
    $prefix = $this->getDatagrid()->getSessionPrefix();
    $use_for = $this->getDatagrid()->getSessionUseFor();

    if (isset($params[$key])) {
      // Set default value
      if ($params[$key] == -1) {
        $params[$key] = $default;
      }
      // Load from session
      if ($params[$key] === NULL) {
        if (in_array($key, $use_for)) {
          $params[$key] = $this->session->get($prefix.$key, $default);
        }
      }
      // Save to session
      if (in_array($key, $use_for)) {
        $this->session->set($prefix.$key, $params[$key]);
      }

      return $params[$key];
    }

    return $default;
  }

  public function getQueryParams($var) {
    if ($this->getDatagrid()->getQueryEncode() === 'json') {
      $queryParams = json_decode($var, TRUE);
      if ($queryParams === NULL) {
        $queryParams = array();
      } else {
        foreach ($queryParams as $key => $value) {
          $queryParams[$key] = urldecode($value);
        }
      }
    } else {
      throw new \Exception('No other query encoding implemented yet!');
    }

    return $queryParams;
  }

  public function getQueryString($vars) {
    if ($this->getDatagrid()->getQueryEncode() === 'json') {
      foreach ($vars as $key => $value) {
        $vars[$key] = urlencode($value);
      }
      $queryString = json_encode($vars, JSON_FORCE_OBJECT);
    } else {
      throw new \Exception('No other query decoding implemented yet!');
    }

    return $queryString;
  }


  private function getNumber() {
    if ($this->results) {
      return count($this->results);
    }

    if ($this->qb) {
      $qbNum = clone $this->qb;
      $rootAlias = reset($qbNum->getRootAliases());
      $qbNum->select($qbNum->expr()->countDistinct($rootAlias.'.id'));
      $qbNum->resetDQLPart('orderBy');

      $query = $qbNum->getQuery();
      $query->useResultCache(
        $this->getDatagrid()->getCacheEnabled(),
        $this->getDatagrid()->getCacheSeconds(),
        $this->getDatagrid()->getCachePrefix().'_scalar'
      );

      return $query->getSingleScalarResult();
    }

    return NULL;
  }

  private function getResults() {
    if ($this->results) {
      return $this->results;
    }

    if ($this->qb) {
      $qbRes = clone $this->qb;
      $qbRes->setFirstResult($this->getDatagrid()
        ->getPagination()
        ->getOffset());
      $qbRes->setMaxResults($this->getDatagrid()->getMaxEntriesPerPage());

      $query = $qbRes->getQuery()
        ->useResultCache($this->getDatagrid()
        ->getCacheEnabled(), $this->getDatagrid()
        ->getCacheSeconds(), $this->getDatagrid()
        ->getCachePrefix().'_result');

      return $query->getResult();
    }

    return new ArrayCollection();
  }

  /**
   * Returns the calculated paginated datagrid.
   *
   * @return \HBM\DatagridBundle\Model\Datagrid
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
    $pagination->setPageCurrent(max(array(
      1,
      min(array($pagination->getPageCurrent(), $pagination->getPageMax()))
    )));

    $pagination->setOffset(max(array(
      0,
      ($pagination->getPageCurrent() - 1) * $max_entries
    )));

    // Calculate numbers
    $pagination->setNumberFrom(0);
    $pagination->setNumberThru(0);
    if ($pagination->getNumberTotal() > 0) {
      $pagination->setNumberFrom(max(array(
        1,
        ($pagination->getPageCurrent() - 1) * $max_entries + 1
      )));
      $pagination->setNumberThru(min(array(
        $pagination->getNumberTotal(),
        $pagination->getNumberFrom() + $max_entries - 1
      )));
    }

    // Calculate pages
    $pagination->setPageFrom(max(array(
      1,
      ($pagination->getPageCurrent() - floor(($max_links - 1) / 2))
    )));
    $pagination->setPageThru(min(array(
      $pagination->getPageMax(),
      ($pagination->getPageFrom() + $max_links - 1)
    )));
    if ($pagination->getPageThru() == $pagination->getPageMax()) {
      $pagination->setPageFrom(max(array(
        1,
        ($pagination->getPageThru() - $max_links)
      )));
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

    if ($pagination->getPageCurrent() != 1) {
      $pagination->setLinkFirst($pagination->createLink(1));
    }
    if ($pagination->getPageCurrent() != $pagination->getPageMax()) {
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
          $new_sortations = $sortations;
          if (!$datagrid->getMultiSort()) {
            $new_sortations = [];
          }

          if (isset($sortations[$cell->getSortKey()]) && ($sortations[$cell->getSortKey()] === 'asc')) {
            // If asc is set, offer desc as next direction
            $new_sortations[$cell->getSortKey()] = 'desc';
          } elseif (isset($sortations[$cell->getSortKey()]) && ($sortations[$cell->getSortKey()] === 'desc')) {
            // If desc is set, unset direction
            unset($new_sortations[$cell->getSortKey()]);
          } else {
            // If nothing is set, offer as as next direction
            $new_sortations[$cell->getSortKey()] = 'asc';
          }

          $params = [$datagrid->getParamNameSortation() => $this->getQueryString($new_sortations)];
          $cell->setTheadLink(new RouteLink($params, $datagrid->getRoute()));
        }
      }
    }
  }

}

?>
