# HBM Datagrid Bundle

## Team

### Developers
Christian Puchinger - christian.puchinger@burda.com

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require burdanews/symfony-bundle-datagrid
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

With Symfony 4 the bundle is enabled automatically for all environments (see `config/bundles.php`). 


### Step 3: Configuration

```yml
hbm_datagrid:
    bootstrap:
        version:  'v4'
        sizes:
            btn: 'sm'
            btn_group: 'sm'
            input_group: 'sm'
            table: 'sm'
            pagination: 'sm'
        classes:
            btn: 'btn btn-secondary'
            btn_group: 'btn-group'
            input_group: 'input-group'
            navbar: 'navbar navbar-light bg-light navbar-expand-sm mb-3'
            table: 'table table-hover table-bordered'
            pagination: 'pagination justify-content-center'
            page_item: 'page-item'
            page_link: 'page-link'
        icons:
            # Sortation
            sort_asc: 'fa fa-sort-amount-asc'
            sort_desc: 'fa fa-sort-amount-desc'
            # Navigation
            search: 'fa fa-search'
            reset: 'fa fa-bolt'
            expand: 'fa fa-expand'
            compress: 'fa fa-compress'
            # Pagination
            page_first: 'fa fa-fast-backward'
            page_prev: 'fa fa-step-backward'
            page_next: 'fa fa-step-forward'
            page_last: 'fa fa-fast-forward'
    session:
        prefix:  'hbm_datagrid:'
        use_for: ['num', 'sort', 'extended']
    query:
        encode: 'json'
        param_names:
            current_page: "page"
            max_entries:  "num"
            sortation:    "sort"
            search:       "search"
            extended:     "extended"
    datagrid:
        sort: true
        multi_sort: true
        max_entries_per_page: 10
    cache:
        enabled: false
        seconds: 300
        prefix: "datagrid"
    pagination:
        max_links_per_page: 10
        show_first: true
        show_prev:  true
        show_next:  true
        show_last:  true
        show_sep:   true
        template: '@HBMDatagrid/Pagination/pagination.html.twig'
    menu:
        show: true
        show_search: true
        search_fields: ~
        show_reset: true
        show_export: false
        exports_selection: ['csv', 'xlsx', 'json']
        exports_resources:
            - { key: max_execution_time, value: 300 }
            - { key: memory_limit, value: '1G' }
        show_range: true
        show_header: true
        show_max_entries_selection: true
        max_entries_selection: [5, 10, 20, 50, 100]
        template: '@HBMDatagrid/Menu/navbar.html.twig'
```

## Usage

### Simple datagrid

```php
// src/HBM/FooBundle/Controller/BarController.php

  public function indexAction($page, $mode) {

    // ...
    
    $om = $this->container->get('doctrine')->getManager();
    $qb = $om->getRepository('HBMFooBundle:Bar')->createQueryBuilder('b');
    
    $datagridHelper = $this->getDatagridHelper();
    $datagridHelper->setQueryBuilder($qb);
    $datagridHelper->createSimpleDatagrid(
      new Route('name_of_a_route', [ 'page' => $page ]),
      [ 'page' => $page, 'num' => 12 ]
    );
    
    return $this->render('HBMFooBundle:Bar:index.html.twig', [
      'datagrid' => $datagridHelper->paginate(),
    ]);
  }

```

```twig

// src/HBM/FooBundle/Resources/views/Bar/index.html.twig

{% for item in datagrid.results %}
    // Output item as you like.
{% endfor %}

{% include '@HBMDatagrid/Pagination/pagination.html.twig' with { 'datagrid': datagrid, 'pagination': datagrid.pagination } only %}
      
```

### Advanced datagrid (basic example)

```php

// src/HBM/FooBundle/Controller/BarController.php

  public function listAction($page, $num, $sort) {
    // DEFAULTS
    $defaults = [
      'page' => 1,
      'num' => 50,
      'sort' => NULL,
    ];

    // DATAGRID
    $datagridHelper = $this->getDatagridHelper();
    $datagridHelper->initDatagrid('name_of_a_route', $defaults, $page, $num, $sort);

    // QUERY BUILDER
    $om = $this->container->get('doctrine')->getManager();
    $qb = $om->getRepository('HBMFooBundle:Bar')->createQueryBuilder('b');
    $datagridHelper->setQueryBuilder($qb);

    // MISC
    $datagridHelper->getDatagrid()->setCells($this->getTableCellsList());

    return $this->render('HBMFooBundle:Bar:list.html.twig', [
      'datagrid' => $datagridHelper->paginate(),
    ]);
  }

```

### Advanced datagrid (extended example)

With search fields, extended mode and export.

```php

// src/HBM/FooBundle/Controller/BarController.php

  public function listAction(Request $request, $page, $num, $sort, $search, $extended) {
    // DEFAULTS
    $defaults = [
      'page' => 1,
      'num' => 50,
      'sort' => NULL,
      'search' => NULL,
      'extended' => NULL,
    ];

    // SEARCH FIELDS
    $searchFields = [
      'value1'  => ['type' => 'text',   'label' => 'Value 1'],
      'value2'  => ['type' => 'number', 'label' => 'Value 2'],
      'value3'  => ['type' => 'select', 'label' => 'Value 3', 'extended' => 1, 'values' => ['yes' => 'with something', 'no' => 'without something']],
    ];

    // DATAGRID
    $datagridHelper = $this->container->get('hbm.helper.datagrid')
    $datagridHelper->setSession($this->getSession(), 'an_additional_session_prefix:');
    $datagridHelper->initDatagrid('name_of_a_route', $defaults, $page, $num, $sort, $search, $extended);

    // SEARCH
    if ($res = $datagridHelper->handleSearch($request, $searchFields)) return $res;

    // QUERY BUILDER
    $om = $this->container->get('doctrine')->getManager();
    $qb = $om->getRepository('HBMFooBundle:Bar')->createQueryBuilder('b');
        
    $qb = $this->prepareQueryBuilderList($datagridHelper->getSortations(), $datagridHelper->getSearchValues());
    $datagridHelper->setQueryBuilder($qb);

    // MISC
    $datagridHelper->getDatagrid()->setCells($this->getTableCells());
    $datagridHelper->getDatagrid()->getMenu()->setSearchFields($searchFields);

    // EXPORT
    if ($res = $datagridHelper->handleExport($request, 'Orders_'.date('Y-m-d'), $om, $this->getSession()->getFlashBag())) return $res;

    return $this->render('HBMFooBundle:Bar:list.html.twig', [
      'datagrid' => $datagridHelper->paginate(),
    ]);
  }
```

Create a query builder that suits your needs. You have to take care of your search values and sortations.

```php
  protected function prepareQueryBuilderList($sortations, $searchValues) {
    $searchValue1 = [];
    if (isset($searchValues['value1'])) {
      $searchValue1 = array_diff(array_map('trim', explode(' ', $searchValues['value1'])), ['']);
    }
  
    $searchValue2 = [];
    if (isset($searchValues['value2']) && !empty($searchValues['value2'])) {
      $searchValue2 = array_diff(array_map('trim', explode(' ', $searchValues['value2'])), ['']);
    }
  
    $om = $this->container->get('doctrine')->getManager();
    $qb = $om->getRepository('HBMFooBundle:Bar')->search($searchValue1, $searchValue2);
  
    if (isset($searchValues['value3']) && ($searchValues['value3'] === 'yes')) {
      $qb->andWhere('b.fieldXYZ IS NOT NULL');
    } elseif (isset($searchValues['value3']) && ($searchValues['value3'] === 'no')) {
      $qb->andWhere('b.fieldXYZ IS NULL');
    }
  
    // QUERY BUILDER SORT
    if (count($sortations) == 0) {
      $qb->addOrderBy('b.id', 'DESC');
    }
    foreach ($sortations as $key => $value) {
      $qb->addOrderBy($key, $value);
    }
  
    return $qb;
  }

```

The base template will render header (with search and export options), the datagrid table itself and the pagination.

```twig

// src/HBM/FooBundle/Resources/views/Bar/list.html.twig

{% include '@HBMDatagrid/base.html.twig' with { 'datagrid': datagrid } only %}

```

If `session` is configured to be used, `num`, `sort` and `extended` will be loaded from session when no url parameter are provided.

```yml

name_of_a_route:
    path:     /list/{page}/{num}/{sort}/{search}/{extended}
    defaults: { _controller: HBMFooBundle:Bar:list, page: 1, num: null, sort: null, search: null, extended: null }
    requirements:
        page: -?\d+
        num:  -?\d+

```

### Cells (full example)

Usage: `new TableCell($key, $label, $route, $visibility, $options)`

#### Visiblities:

- `TableCell::VISIBLE_NORMAL` (only visible in normal mode)
- `TableCell::VISIBLE_NORMAL_EX` (visible in normal mode and export)
- `TableCell::VISIBLE_EXTENDED` (only visible in extended mode)
- `TableCell::VISIBLE_EXTENDED_EX` (visible in extended mode and export)
- `TableCell::VISIBLE_EXPORT` (only visible in export)
- `TableCell::VISIBLE_BOTH` (visible in normal and extended mode)
- `TableCell::VISIBLE_ALL` (always visible)


#### Valid options are:

- `value` => string|callback
- `th_attr` => string|array
- `td_attr` => string|array
- `a_attr` => string|array
- `sort_key` => string|array
- `sort_key_sep` => string
- `label_pos` => string
- `params` => array|callback
- `template` => string|callback
- `template_params` => array|callback
- `format` => string

#### Displaying values:

If a `template` option is provided, it will be used. Otherwise the default value will be used and the value will be parsed.

#### Parsing values:

If no `value` option is provided, `$key` will be used to get a value: `$obj->{'get'.ucfirst($key)}`
If the returned value is an `DateTime` instance, it can be formated with the `format` option.

```php

  protected function getTableCells(){
    return [
      // Simple text/numeric value (not sortable).
      new TableCell('field', 'Column 1', NULL, TableCell::VISIBLE_ALL, [
      ]),
      
      // Simple text/numeric value (sortable).
      new TableCell('field', 'Column 2', NULL, TableCell::VISIBLE_EXTENDED, [
        "sort_key" => "g.field",
      ]),

      // Button (with static value)
      new TableCell('field', 'Column 3', new Route('name_of_another_route'), FALSE, [
        'value' => '<span class="glyphicon glyphicon-wrench"></span>',
        'a_attr' => ['class' => 'btn btn-primary btn-xs'],
        'params' => function(Bar $obj) {
          return ['id' => $obj->getId()];
        },
      ]),

      // Button (with dynamic value)
      new TableCell('field', 'Column 4', new Route('name_of_another_route'), TableCell::VISIBLE_NORMAL, [
        'a_attr' => ['class' => 'btn btn-primary btn-xs'],
        'params' => function(Bar $obj) {
          return ['id' => $obj->getId()];
        },
        "sort_key" => "g.id",
      ]),
      
      // Template ( "obj" is always available in the template).
      new TableCell('field', 'Column 5', NULL, TableCell::VISIBLE_BOTH, [
        'template' => 'HBMFooBundle:Bar:table-column-template1.html.twig',
      ]),

      // Template (with additional params).
      new TableCell('field', 'Column 6', NULL, TableCell::VISIBLE_BOTH, [
        'template' => 'HBMFooBundle:Bar:table-column-template1.html.twig',
        'templateParams' => ['mode' => 'thisAndthat'],
        'th_attr' => ['style' => 'width:265px;']
      ]),

      // Value callable.
      new TableCell('field', 'Column 7:<br />Subtitle', NULL, TableCell::VISIBLE_EXTENDED, [
        'value' => function(Bar $obj) {
          return implode(' / ', [$obj->someFunction(), $obj->someOtherFunction()]);
        },
      ]),

      // Value callable with multiple sortation possibilities.
      new TableCell('views', 'Column 8:<br />', NULL, TRUE, [
        'value' => function(Bar $obj) {
          return implode(' / ', [$obj->someFunction(), $obj->someOtherFunction(), $obj->someOtherFunction2()]);
        },
        'sort_key' => ['b.field1' => 'Bla1', 'b.field2' => 'Bla2', 'b.field3' => 'Bla3'],
        'sort_key_sep' => '&nbsp;|&nbsp;'
      ]),
    ];
  }

```
