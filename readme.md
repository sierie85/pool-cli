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

### Create DAO Command (*alpha*)

```bash
php vendor/bin/pool-cli create_dao
```

### List all schemes / routes (*todo*)

```bash
php vendor/bin/pool-cli show_routes
```

### List all GUI's (*todo*)

```bash
php vendor/bin/pool-cli list_guis
```

## Tests

### Run Test (dev-only)

```bash
./vendor/bin/phpunit tests
```