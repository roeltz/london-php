<?php

namespace London\Data;

use London\Data\Source\DataSource;

class Collection {

	public $name;

	public $alias;
	
	protected $dataSource;

	static function from($collection) {
		if ($collection instanceof self) {
			return $collection;
		} else {
			return new Collection($collection);
		}
	}

	function __construct(string $name, string $alias = null, DataSource $dataSource = null) {
		$this->name = $name;
		$this->alias = $alias;
		$this->dataSource = $dataSource;
	}

	function drop() {
		$this->dataSource->dropCollection($this);
	}

	function field(string $name) {
		return new Field($name, $this);
	}

	function query(): Query {
		return $this->dataSource->newQuery()->from($this);
	}

	function save(array $values, string $sequence = null) {
		$this->dataSource->save($values, $this, $sequence);
	}
}