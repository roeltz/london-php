<?php

namespace London\Data;

class Field {

	public $collection;
	
	public $name;

	static function from($field, $collection = null) {
		if ($field instanceof static) {
			return $field;
		} else {
			return new static($field, $collection);
		}
	}

	function __construct($name, Collection $collection = null) {
		$this->name = $name;
		$this->collection = $collection;
	}

	function equals($field) {
		$field = self::from($field);
		return $this->name === $field->name && @$this->collection->name === @$field->collection->name;
	}
}
