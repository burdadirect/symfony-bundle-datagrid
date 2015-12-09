# hbm_datagrid

## Team

### Developers
Christian Puchinger - puchinger@playboy.de

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require burdanews/datagrid-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new HBM\DatagridBundle\HBMDatagridBundle(),
        );

        // ...
    }

    // ...
}
```

### Configuration

```yml
hbm_datagrid:
    session:
        prefix:  'hbm_datagrid:'
        use_for: ['num', 'sort']
    query:
        encode: 'json'
        param_names:
            current_page: "page"
            max_entries:  "num"
            sortation:    "sort"
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
        template: 'HBMDatagridBundle:Pagination:pagination.html.twig'
    menu:
        show: true
        show_search: true
        search_fields: ~
        show_reset: true
        show_range: true
        show_header: true
        show_max_entries_selection: true
        max_entries_selection: [5, 10, 20, 50, 100]
        template: 'HBMDatagridBundle:Menu:navbar.html.twig'
```
