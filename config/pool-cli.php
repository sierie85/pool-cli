<?php
declare(strict_types=1);

const BASE_DIR = __DIR__ . '/..';
const SRC_DIR = BASE_DIR . '/src';
const DAO_DIR = SRC_DIR . '/daos';

const DAO_NAMESPACE = 'CLI_Pool\daos';

const DATABASE_CONNECTIONS = [
    'default' => [
        'host' => 'localhost',
        'port' => '3306',
        'user' => 'root',
        'password' => '',
    ],
    'abc.com' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'user' => 'root',
        'password' => '',
    ],
];