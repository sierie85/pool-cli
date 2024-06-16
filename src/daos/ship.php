<?php
declare(strict_types=1);

//namespace ?\daos;

class Ship
{
    protected string $database = 'oo_battle';
    protected string $table = 'ship';
    private string $pk = 'id';

    /**
     * columns of table ship
     *
     * id (int(11))  extra: auto_increment primaryKey
     * name (varchar(255))
     * weapon_power (int(4))
     * jedi_factor (int(4))
     * strength (int(4))
     * team (varchar(10))
     */
    private array $columns = [
        'id',
        'name',
        'weapon_power',
        'jedi_factor',
        'strength',
        'team'];
}
