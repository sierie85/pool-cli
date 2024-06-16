<?php
declare(strict_types=1);

//namespace ?\daos;

class Entries
{
	protected string $database = 'guestbook';
	protected string $table = 'entries';
	private string $pk = 'id';
	
	/**
	 * columns of table entries
	 *
	 * id (int(11))  extra: auto_increment primaryKey
	 * name (varchar(200))   
	 * text (varchar(10000))   
	 * date (date)   
	 */
	private array $columns = [
		'id',
		'name',
		'text',
		'date',
	];
}
