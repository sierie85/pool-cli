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
  "require-dev": {
    "sierie85/pool-cli": "^1.1.0"
  }
}
```

## Configuration

a *config-pool-cli.yaml* file is required in the root of your project or in ./config. If you run a command and no
config file is found, the cli will try to create one.
The following is an example of a config file:

```yaml
source_directory: 'project_example_folder'
external_commands_directory: 'project_example_folder/commands'
database_connections:
  default:
    host: 'localhost'
    port: '3306'
    database: 'example'
    user: 'username'
    password: 'password'
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

## Extend CLI with custom commands

you can extend the CLI with custom commands by creating a folder and class in the external_commands_directory defined in
the config
file. The following structure and naming must be provided:

```
/commandDirectory/NewCommand/NewCommand.php
```

The class must extend the Symfony::Command class and implement the execute method.

## Tests

### Run Test in non production environment

```bash
php vendor/bin/phpunit tests -c tests/phpunit.xml
```