<?php

namespace London\Data\Query;

use London\Data\Field;

class RangeExpression implements Expression {

	public $field;

	public $max;
	
	public $min;

	function __construct(Field $field, $min, $max) {
		$this->field = $field;
		$this->min = $min;
		$this->max = $max;
	}

	function __clone() {
		$this->field = clone $this->field;
	}
}
