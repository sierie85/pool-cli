<?php
declare(strict_types=1);

//namespace ?\daos;

class Pages
{
	protected string $database = 'cms';
	protected string $table = 'pages';
	private string $pk = 'id';
	
	/**
	 * columns of table pages
	 *
	 * id (int(11))  extra: auto_increment primaryKey
	 * slug (varchar(1000))   
	 * title (varchar(1000))   
	 * content (text)   
	 */
	private array $columns = [
'id',
'slug',
'title',
'content'
	];
}
