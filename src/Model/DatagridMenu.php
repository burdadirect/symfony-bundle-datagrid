<?php

namespace HBM\DatagridBundle\Model;

class DatagridMenu
{
    /** @var Datagrid */
    private $datagrid;

    /* CONFIG ****************************************************************** */

    /** @var bool */
    private $show;

    /** @var bool */
    private $showSearch;

    /** @var array */
    private $searchFields;

    /** @var array */
    private $searchValues;

    /** @var bool */
    private $showExtended;

    /** @var bool */
    private $showColumns;

    /** @var array */
    private $columnsSelection;

    /** @var bool */
    private $showExport;

    /** @var array */
    private $exportsSelection;

    /** @var array */
    private $exportsResources;

    /** @var bool */
    private $showReset;

    /** @var bool */
    private $showRange;

    /** @var bool */
    private $showHeader;

    /** @var bool */
    private $showMaxEntriesSelection;

    /** @var array */
    private $maxEntriesSelection;

    /** @var string */
    private $template;

    /* ROUTING ***************************************************************** */

    /** @var Route */
    private $route;

    /** @var Route */
    private $routeReset;

    /** @var Route */
    private $routeExtended;

    /** @var Route */
    private $routeColumns;

    /** @var Route */
    private $routeSearch;

    /* LINKS ******************************************************************* */

    /** @var array; */
    private $links = [];

    /* CONSTRUCTOR *********************************************************** */

    public function __construct($config)
    {
        $this->setShow($config['menu']['show']);
        $this->setShowSearch($config['menu']['show_search']);
        $this->setSearchFields($config['menu']['search_fields']);
        $this->setShowReset($config['menu']['show_reset']);
        $this->setShowExtended($config['menu']['show_extended']);
        $this->setShowColumns($config['menu']['show_columns']);
        $this->setColumnsSelection($config['datagrid']['columns_selection'] ?? []);
        $this->setShowExport($config['menu']['show_export']);
        $this->setExportsSelection($config['menu']['exports_selection']);
        $this->setExportsResources($config['menu']['exports_resources']);
        $this->setShowRange($config['menu']['show_range']);
        $this->setShowHeader($config['menu']['show_header']);
        $this->setShowMaxEntriesSelection($config['menu']['show_max_entries_selection']);
        $this->setMaxEntriesSelection($config['menu']['max_entries_selection']);
        $this->setTemplate($config['menu']['template']);
    }

    /* GETTER/SETTER *********************************************************** */

    public function setDatagrid($datagrid)
    {
        $this->datagrid = $datagrid;
    }

    public function getDatagrid()
    {
        return $this->datagrid;
    }

    public function setShow($show)
    {
        $this->show = $show;
    }

    public function getShow()
    {
        return $this->show;
    }

    public function setShowSearch($showSearch)
    {
        $this->showSearch = $showSearch;
    }

    public function getShowSearch()
    {
        return $this->showSearch;
    }

    public function setSearchFields($searchFields)
    {
        $this->searchFields = $searchFields;
    }

    public function getSearchFields()
    {
        return $this->searchFields;
    }

    public function setSearchValues($searchValues)
    {
        $this->searchValues = $searchValues;
    }

    public function getSearchValues()
    {
        return $this->searchValues;
    }

    public function setShowExtended($showExtended)
    {
        $this->showExtended = $showExtended;
    }

    public function getShowExtended()
    {
        return $this->showExtended;
    }

    public function setShowColumns($showColumns)
    {
        $this->showColumns = $showColumns;
    }

    public function getShowColumns()
    {
        return $this->showColumns;
    }

    public function setColumnsSelection($columnsSelection)
    {
        $this->columnsSelection = $columnsSelection;
    }

    public function getColumnsSelection()
    {
        return $this->columnsSelection;
    }

    public function setShowExport($showExport)
    {
        $this->showExport = $showExport;
    }

    public function getShowExport()
    {
        return $this->showExport;
    }

    public function setExportsSelection($exportsSelection)
    {
        $this->exportsSelection = $exportsSelection;
    }

    public function getExportsSelection()
    {
        return $this->exportsSelection;
    }

    public function setExportsResources($exportsResources)
    {
        $this->exportsResources = $exportsResources;
    }

    public function getExportsResources()
    {
        return $this->exportsResources;
    }

    public function setShowReset($showReset)
    {
        $this->showReset = $showReset;
    }

    public function getShowReset()
    {
        return $this->showReset;
    }

    public function setShowRange($showRange)
    {
        $this->showRange = $showRange;
    }

    public function getShowRange()
    {
        return $this->showRange;
    }

    public function setShowHeader($showHeader)
    {
        $this->showHeader = $showHeader;
    }

    public function getShowHeader()
    {
        return $this->showHeader;
    }

    public function setShowMaxEntriesSelection($showMaxEntriesSelection)
    {
        $this->showMaxEntriesSelection = $showMaxEntriesSelection;
    }

    public function getShowMaxEntriesSelection()
    {
        return $this->showMaxEntriesSelection;
    }

    public function setMaxEntriesSelection($maxEntriesSelection)
    {
        $this->maxEntriesSelection = $maxEntriesSelection;
    }

    public function getMaxEntriesSelection()
    {
        return $this->maxEntriesSelection;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setRoute(Route $route)
    {
        $this->route = $route;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRouteExtended(Route $routeExtended)
    {
        $this->routeExtended = $routeExtended;
    }

    public function getRouteExtended()
    {
        return $this->routeExtended;
    }

    public function setRouteColumns(Route $routeColumns)
    {
        $this->routeColumns = $routeColumns;
    }

    public function getRouteColumns()
    {
        return $this->routeColumns;
    }

    public function setRouteReset(Route $routeReset)
    {
        $this->routeReset = $routeReset;
    }

    public function getRouteReset()
    {
        return $this->routeReset;
    }

    public function setRouteSearch(Route $routeSearch)
    {
        $this->routeSearch = $routeSearch;
    }

    public function getRouteSearch()
    {
        return $this->routeSearch;
    }

    public function setLinks($links)
    {
        $this->links = $links;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function addLink($link)
    {
        $this->links[] = $link;
    }

    /* CUSTOM **************************************************************** */

    public function createLink($value)
    {
        $routeLink = new RouteLink([$this->datagrid->getParamNameMaxEntries() => $value], $this->getRoute());
        $routeLink->setValue($value);

        return $routeLink;
    }

    public function getSearchValue($key)
    {
        if (isset($this->searchValues[$key])) {
            return $this->searchValues[$key];
        }

        return null;
    }

    public function getSearchFieldsSorted(): array
    {
        $searchFieldsSorted = $this->getSearchFields();

        // Fill missing weights.
        $weight = 1;
        foreach ($searchFieldsSorted as &$searchField) {
            $searchField['weight'] ??= $weight++;
        }

        // Get weights as array.
        $weights = array_column($searchFieldsSorted, 'weight');

        // Sort search fields ascending according to weight.
        array_multisort($weights, SORT_ASC, $searchFieldsSorted);

        return $searchFieldsSorted;
    }

    public function __toString()
    {
        $string = '';
        $string .= 'MENU-VARS:' . "\n";
        $string .= 'show: ' . $this->getShow() . "\n";
        $string .= 'showSearch: ' . $this->getShowSearch() . "\n";
        $string .= 'showExport: ' . $this->getShowExport() . "\n";
        $string .= 'showExtended: ' . $this->getShowExtended() . "\n";
        $string .= 'showColumns: ' . $this->getShowColumns() . "\n";
        $string .= 'showMaxEntriesSelection: ' . $this->getShowMaxEntriesSelection() . "\n";
        $string .= 'maxEntriesSelection: ' . json_encode($this->getMaxEntriesSelection()) . "\n";
        $string .= "\n";
        $string .= 'MENU-ROUTE:' . "\n";
        $string .= $this->route . "\n";
        $string .= "\n";
        $string .= 'MENU-ROUTE-SEARCH:' . "\n";
        $string .= $this->routeSearch . "\n";
        $string .= "\n";
        $string .= 'MENU-ROUTE-RESET:' . "\n";
        $string .= $this->routeReset . "\n";
        $string .= "\n";
        $string .= 'MENU-ROUTE-EXTENDED:' . "\n";
        $string .= $this->routeExtended . "\n";
        $string .= "\n";
        $string .= 'MENU-ROUTE-COLUMNS:' . "\n";
        $string .= $this->routeColumns . "\n";
        $string .= "\n";
        $string .= 'MENU-LINKS:' . "\n";
        foreach ($this->links as $link) {
            $string .= $link . "\n";
        }

        return $string;
    }
}
