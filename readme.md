# Pool-CLI

adds CLI commands to pool-framework

## install

add following to composer.json

```
...
"repositories": [
    {
      "type": "vcs",
      "url":  "git@github.com:sierie85/pool_cli.git"
    }
  ],
...

"require": {
    ...
    "sierie85/pool-cli": "dev-main"
    ...
  }
```

## usage

### Create GUI Command (*alpha*)

```bash
php vendor/bin/pool-cli create_gui
```

todo: add options --no-comments --no-styles --no-scripts etc.

### Create DAO Command (*alpha*)

```bash
php vendor/bin/pool-cli create_dao
```

### List all schemes / routes (*alpha*)

```bash
php vendor/bin/pool-cli list_schemes
```

### List all GUI's (*alpha*)

```bash
php vendor/bin/pool-cli list_guis
```

### List all ajaxRequest's (*todo*)

(n)ajaxRequest per (n)gui in select project

```bash
php vendor/bin/pool-cli list_ajax
```

## Tests

### Run Test (dev-only)

```bash
./vendor/bin/phpunit tests
```