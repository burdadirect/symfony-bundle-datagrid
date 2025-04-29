<?php

namespace HBM\DatagridBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use HBM\DatagridBundle\Traits\ParseAttrTrait;
use HBM\TwigAttributesBundle\Utils\HtmlAttributes;

class Datagrid
{
    use ParseAttrTrait;

    /** @var string */
    private $translationDomainVariableTexts;

    /** @var string */
    private $translationDomainFixedTexts;

    /** @var array */
    private $bootstrap;

    /** @var array */
    private $icons;

    /* SESSION ***************************************************************** */

    /** @var string */
    private $sessionPrefix;

    /** @var array */
    private $sessionUseFor;

    /* DEFAULTS **************************************************************** */

    /** @var int */
    private $maxEntriesPerPage;

    /* PARAM NAMES ************************************************************* */

    /** @var string */
    private $paramNameCurrentPage;

    /** @var string */
    private $paramNameMaxEntries;

    /** @var string */
    private $paramNameSortation;

    /** @var string */
    private $paramNameSearch;

    /** @var string */
    private $paramNameExtended;

    /** @var string */
    private $paramNameColumns;

    /* CONFIG ****************************************************************** */

    /** @var bool */
    private $extended;

    /** @var array */
    private $columnsOverride;

    /** @var bool */
    private $sort;

    /** @var bool */
    private $multiSort;

    /* CACHE ******************************************************************* */

    /** @var bool */
    private $cacheEnabled;

    /** @var int */
    private $cacheSeconds;

    /** @var string */
    private $cachePrefix;

    /* ROUTING ***************************************************************** */

    /** @var Route */
    private $route;

    /* COLUMNS/ROWS ************************************************************ */

    /** @var array */
    private $sortations = [];

    /** @var array */
    private $cells;

    /** @var array|callable|HtmlAttributes|string */
    private $rowAttr;

    /** @var array|callable|HtmlAttributes|string */
    private $tableAttr;

    /** @var array|callable|HtmlAttributes|string */
    private $tableHeadAttr;

    /** @var array|callable|HtmlAttributes|string */
    private $tableBodyAttr;

    /** @var \Doctrine\Common\Collections\Collection */
    private $results;

    /* PAGINATION/MENU ********************************************************* */

    /** @var DatagridPagination */
    private $pagination;

    /** @var DatagridMenu */
    private $menu;

    /* CONSTRUCTOR ************************************************************* */

    public function __construct($config)
    {
        $this->results = new ArrayCollection();

        $this->setTranslationDomainVariableTexts($config['translation_domain']['variable_texts']);
        $this->setTranslationDomainFixedTexts($config['translation_domain']['fixed_texts']);
        $this->setBootstrap($config['bootstrap']);
        $this->setIcons($config['icons']);

        $this->setSort($config['datagrid']['sort']);
        $this->setMultiSort($config['datagrid']['multi_sort']);
        $this->setColumnsOverride($config['datagrid']['columns_override'] ?? []);
        $this->setMaxEntriesPerPage($config['datagrid']['max_entries_per_page']);

        $this->setCacheEnabled($config['cache']['enabled']);
        $this->setCacheSeconds($config['cache']['seconds']);
        $this->setCachePrefix($config['cache']['prefix']);

        $this->setParamNameCurrentPage($config['query']['param_names']['current_page']);
        $this->setParamNameMaxEntries($config['query']['param_names']['max_entries']);
        $this->setParamNameSortation($config['query']['param_names']['sortation']);
        $this->setParamNameSearch($config['query']['param_names']['search']);
        $this->setParamNameExtended($config['query']['param_names']['extended']);
        $this->setParamNameColumns($config['query']['param_names']['columns']);

        $this->setSessionPrefix($config['session']['prefix']);
        $this->setSessionUseFor($config['session']['use_for']);
    }

    /* GETTER/SETTER ********************************************************* */

    public function setTranslationDomainVariableTexts($td)
    {
        $this->translationDomainVariableTexts = $td;
    }

    public function getTranslationDomainVariableTexts()
    {
        return $this->translationDomainVariableTexts;
    }

    public function setTranslationDomainFixedTexts($td)
    {
        $this->translationDomainFixedTexts = $td;
    }

    public function getTranslationDomainFixedTexts()
    {
        return $this->translationDomainFixedTexts;
    }

    public function setBootstrap($bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public function getBootstrap()
    {
        return $this->bootstrap;
    }

    public function setIcons($icons)
    {
        $this->icons = $icons;
    }

    public function getIcons()
    {
        return $this->icons;
    }

    public function setSessionPrefix($sessionPrefix)
    {
        $this->sessionPrefix = $sessionPrefix;
    }

    public function getSessionPrefix()
    {
        return $this->sessionPrefix;
    }

    public function setSessionUseFor($sessionUseFor)
    {
        $this->sessionUseFor = $sessionUseFor;
    }

    public function getSessionUseFor()
    {
        return $this->sessionUseFor;
    }

    public function setMaxEntriesPerPage($maxEntriesPerPage)
    {
        $this->maxEntriesPerPage = max(1, (int) $maxEntriesPerPage);
    }

    public function getMaxEntriesPerPage()
    {
        return $this->maxEntriesPerPage;
    }

    public function setCacheEnabled($cacheEnabled)
    {
        $this->cacheEnabled = $cacheEnabled;
    }

    public function getCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    public function setCacheSeconds($cacheSeconds)
    {
        $this->cacheSeconds = $cacheSeconds;
    }

    public function getCacheSeconds()
    {
        return $this->cacheSeconds;
    }

    public function setCachePrefix($cachePrefix)
    {
        $this->cachePrefix = $cachePrefix;
    }

    public function getCachePrefix()
    {
        return $this->cachePrefix;
    }

    public function setParamNameCurrentPage($paramName)
    {
        $this->paramNameCurrentPage = $paramName;
    }

    public function getParamNameCurrentPage()
    {
        return $this->paramNameCurrentPage;
    }

    public function setParamNameMaxEntries($paramName)
    {
        $this->paramNameMaxEntries = $paramName;
    }

    public function getParamNameMaxEntries()
    {
        return $this->paramNameMaxEntries;
    }

    public function setParamNameSortation($paramName)
    {
        $this->paramNameSortation = $paramName;
    }

    public function getParamNameSortation()
    {
        return $this->paramNameSortation;
    }

    public function setParamNameSearch($paramName)
    {
        $this->paramNameSearch = $paramName;
    }

    public function getParamNameSearch()
    {
        return $this->paramNameSearch;
    }

    public function setParamNameExtended($paramName)
    {
        $this->paramNameExtended = $paramName;
    }

    public function getParamNameExtended()
    {
        return $this->paramNameExtended;
    }

    public function setParamNameColumns($paramName)
    {
        $this->paramNameColumns = $paramName;
    }

    public function getParamNameColumns()
    {
        return $this->paramNameColumns;
    }

    public function setExtended($extended)
    {
        $this->extended = (bool) $extended;
    }

    public function setColumnsOverride($columnsOverride)
    {
        $this->columnsOverride = $columnsOverride;
    }

    public function getColumnsOverride()
    {
        return $this->columnsOverride;
    }

    public function getExtended()
    {
        return $this->extended;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setMultiSort($multiSort)
    {
        $this->multiSort = $multiSort;
    }

    public function getMultiSort()
    {
        return $this->multiSort;
    }

    public function setRoute(Route $route)
    {
        $this->route = $route;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setResults($results)
    {
        $this->results = $results;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function setSortations($sortations)
    {
        $this->sortations = $sortations;
    }

    public function getSortations()
    {
        return $this->sortations;
    }

    public function setCells($cells)
    {
        $this->cells = $cells;
    }

    public function getCells()
    {
        return $this->cells;
    }

    public function setTableAttr($tableAttr)
    {
        $this->tableAttr = $tableAttr;
    }

    public function getTableAttr()
    {
        return $this->tableAttr;
    }

    public function setTableHeadAttr($tableHeadAttr)
    {
        $this->tableHeadAttr = $tableHeadAttr;
    }

    public function getTableHeadAttr()
    {
        return $this->tableHeadAttr;
    }

    public function setTableBodyAttr($tableBodyAttr)
    {
        $this->tableBodyAttr = $tableBodyAttr;
    }

    public function getTableBodyAttr()
    {
        return $this->tableBodyAttr;
    }

    public function setRowAttr($rowAttr)
    {
        $this->rowAttr = $rowAttr;
    }

    public function getRowAttr()
    {
        return $this->rowAttr;
    }

    /**
     * Set pagination
     *
     * @param DatagridPagination $pagination
     *
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
     *
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

    /* CUSTOM ****************************************************************** */

    public function tdVar()
    {
        return $this->getTranslationDomainVariableTexts();
    }

    public function tdFix()
    {
        return $this->getTranslationDomainFixedTexts();
    }

    public function tdSearchField(array $searchField)
    {
      return $searchField['trans_domain'] ?? $this->getTranslationDomainVariableTexts();
    }

    public function tdTableCell(TableCell $tableCell)
    {
        return $tableCell->getOption('trans_domain') ?? $this->getTranslationDomainVariableTexts();
    }

    public function isSorted($key)
    {
        if (isset($this->sortations[$key])) {
            return true;
        }

        return false;
    }

    public function getSortationDirection($key)
    {
        if (isset($this->sortations[$key])) {
            return strtolower($this->sortations[$key]);
        }

        return null;
    }

    public function parseTableAttr(): HtmlAttributes
    {
        $classes = ['datagrid-table'];

        if ($this->getBootstrap()['version'] === 'v4') {
            $classes[] = $this->getBootstrap()['classes']['table'] ?? null;
            $classes[] = $this->getBootstrap()['sizes']['table'] ?? null;
        } else {
            $classes = ['table table-hover table-bordered table-condensed'];
        }

        $attributes = new HtmlAttributes(['class' => $classes]);

        return $this->parseAttr($attributes, $this->getTableAttr());
    }

    public function parseTableHeadAttr(): HtmlAttributes
    {
        $classes = ['datagrid-table-head'];

        if ($this->getBootstrap()['version'] === 'v4') {
            $classes[] = $this->getBootstrap()['classes']['thead'] ?? null;
        }

        $attributes = new HtmlAttributes(['class' => $classes]);

        return $this->parseAttr($attributes, $this->getTableHeadAttr());
    }

    public function parseTableBodyAttr(): HtmlAttributes
    {
        $attributes = new HtmlAttributes(['class' => 'datagrid-table-body']);

        return $this->parseAttr($attributes, $this->getTableBodyAttr());
    }

    public function parseRowAttr($obj, $row): HtmlAttributes
    {
        $attributes = new HtmlAttributes();

        return $this->parseAttr($attributes, $this->getRowAttr(), [$obj, $row]);
    }

    public function __toString()
    {
        $string = '';
        $string .= 'DATAGRID-COLLECTIONS:' . "\n";
        $string .= ($this->cells !== null) ? \count($this->cells) : 'No Cells' . "\n";
        $string .= ($this->results !== null) ? \count($this->results) : 'No Results' . "\n";
        $string .= "\n";
        $string .= 'DATAGRID-VARS:' . "\n";
        $string .= 'maxEntriesPerPage: ' . $this->getMaxEntriesPerPage() . "\n";
        $string .= 'multiSort: ' . $this->getMultiSort() . "\n";
        $string .= 'sort: ' . $this->getSort() . "\n";
        $string .= 'extended: ' . $this->getExtended() . "\n";
        $string .= 'columns: ' . json_encode($this->getColumnsOverride()) . "\n";
        $string .= "\n";
        $string .= 'DATAGRID-PARAM-NAMES:' . "\n";
        $string .= 'currentPage: ' . $this->getParamNameCurrentPage() . "\n";
        $string .= 'maxEntries: ' . $this->getParamNameMaxEntries() . "\n";
        $string .= 'sortation: ' . $this->getParamNameSortation() . "\n";
        $string .= 'search: ' . $this->getParamNameSearch() . "\n";
        $string .= 'extended: ' . $this->getParamNameExtended() . "\n";
        $string .= 'columns: ' . $this->getParamNameColumns() . "\n";
        $string .= "\n";
        $string .= 'DATAGRID-ROUTE:' . "\n";
        $string .= $this->route . "\n";

        return $string;
    }
}
