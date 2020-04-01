<?php

namespace London\Data\Query;

use London\Data\Collection;
use London\Data\Field;
use London\Data\Source\DataSource;
use London\Util\Arrays;

class Query extends ExpressionBuilder {

	public Collection $collection;

	public DataSource $dataSource;

	public $distinct = false;

	public $fields = [];

	public $index;

	public $limit;

	public $offset;

	public $order = [];

	function __construct(DataSource $dataSource, Query $parent = null) {
		parent::__construct($parent);
		$this->dataSource = $dataSource;
	}

	function aggregate(string $operator, $field) {
		return $this->dataSource->aggregate(new Aggregate($operator, Field::from($field)), $this);
	}

	function count() {
		return $this->dataSource->count($this);
	}

	function delete() {
		return $this->dataSource->delete($this);
	}

	function distinct(bool $distinct = true) {
		$this->distinct = $distinct;
		return $this;
	}

	function fields(...$fields) {
		foreach ($fields as $field) {
			$this->fields[] = Field::from($field);
		}
		return $this;
	}

	function from($collection) {
		$this->collection = Collection::from($collection);
		return $this;
	}

	function getAll() {
		if ($this->index && !$this->hasField($this->index)) {
			$this->fields($this->index);
		}

		$result = $this->dataSource->query($this);

		if ($this->index) {
			$result = Arrays::index($result, $this->index->name);
		}

		return $result;
	}

	function getField($field) {
		$field = Field::from($field);
		$this->fields = [$field];

		if ($result = $this->getAll()) {
			foreach ($result as &$item) {
				$item = $item[$field->name];
			}
		}

		return $result;
	}

	function getSingle() {
		$this->n(1);

		if ($result = $this->getAll()) {
			return current($result);
		}
	}

	function getValue($field) {
		$field = Field::from($field);
		$this->fields = [$field];

		if ($result = $this->getSingle()) {
			return $result[$field->name];
		}
	}

	function hasField($field) {
		$field = Field::from($field);

		foreach ($this->fields as $f) {
			if ($f->equals($field)) {
				return true;
			}
		}
	}

	function indexBy($field) {
		$this->index = Field::from($field);
		return $this;
	}

	function limit($limit, $offset) {
		$this->limit = $limit;
		$this->offset = $offset;
		return $this;
	}

	function n($n) {
		$this->limit($n, 0);
		return $this;
	}

	function orderBy($field, int $type = Order::ASC) {
		$this->order[] = new Order(Field::from($field), $type);
		return $this;
	}

	function page($page, $size) {
		$this->limit($size, ($page - 1) * $size);
		return $this;
	}

	function pageCount($size) {
		return ceil($this->count() / ($size > 0 ? $size : 1));
	}

	function update(array $values) {
		return $this->dataSource->update($values, $this);
	}
}