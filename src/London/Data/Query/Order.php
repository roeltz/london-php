<?php

namespace London\Data\Query;

use London\Data\Field;

class Order {

	const ASC = 1;
	const DESC = -1;
	const RANDOM = 0;

	public $field;

	public $type;

	function __construct(Field $field, $type = self::ASC) {
		$this->field = $field;
		$this->type = $type;
	}

	function __clone() {
		$this->field = clone $this->field;
	}
}
