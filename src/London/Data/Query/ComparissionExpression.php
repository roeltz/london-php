<?php

namespace London\Data\Query;

class ComparissionExpression implements Expression {

	const EQ = "=";
	const NE = "<>";
	const LT = "<";
	const LE = "<=";
	const GT = ">";
	const GE = ">=";
	const LIKE = "like";
	const ILIKE = "ilike";
	const REGEX = "regex";

	public $a;

	public $b;

	public $operator;

	function __construct(string $operator, $a, $b) {
		$this->operator = $operator;
		$this->a = $a;
		$this->b = $b;
	}

	function __clone() {
		if (is_object($this->a)) {
			$this->a = clone $this->a;
		}

		if (is_object($this->b)) {
			$this->b = clone $this->b;
		}
	}
}
