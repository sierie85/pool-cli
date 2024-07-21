# Pool-CLI

a tool for enhancing the development workflow with a suite of predefined commands.
Designed to seamlessly integrate with [POOL](https://github.com/manhart/pool), this CLI simplifies and accelerates your
development process.

## Installation

add the following to your composer.json file

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:sierie85/pool_cli.git"
    }
  ],
  "require": {
    "sierie85/pool-cli": "^1.0"
  }
}
```

## Configuration

create a file in your root folder /config/pool-cli.php e.g.:

```php
const BASE_DIR = __DIR__ . '/..';
const SRC_DIR = BASE_DIR . '/src';
const DAO_DIR = SRC_DIR . '/daos';
const DAO_NAMESPACE = 'App\daos';
const DATABASE_CONNECTIONS = [
    'default' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'user' => 'root',
        'password' => 'root',
    ],
];
```

## Basic Usage

you can call the CLI by running the following command in your terminal

```bash
php vendor/bin/pool-cli *command*
```

## Commands

### Create GUI

```bash
php vendor/bin/pool-cli create:gui
```

additionally you can pass the following options:

```bash
php vendor/bin/pool-cli create:gui --no-style # will not create a stylesheet file
php vendor/bin/pool-cli create:gui --no-script # will not create a javascript file
php vendor/bin/pool-cli create:gui --only-class # will only create a class file
```

### Create DAO

```bash
php vendor/bin/pool-cli create:dao
```

### List all schemes / routes

```bash
php vendor/bin/pool-cli list:schemes
```

### List all GUI's

```bash
php vendor/bin/pool-cli list:guis
```

### List all ajaxRequest's

(n)ajaxRequest per (n)gui in select project

```bash
php vendor/bin/pool-cli list:ajax
```

## Tests

### Run Test in non production environment

```bash
php vendor/bin/phpunit tests -c tests/phpunit.xml
```