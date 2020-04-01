<?php

namespace London\Data\Query;

use London\Data\Field;

class Aggregate {

	const AVG = "avg";
	const MAX = "max";
	const MIN = "min";
	const SUM = "sum";

	public $alias;

	public $field;

	public $operator;

	function __construct(string $operator, Field $field, string $alias = null) {
		$this->operator = $operator;
		$this->field = $field;
		$this->alias = $alias;
	}
}
