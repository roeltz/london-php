<?php

namespace London\Data\Query;

class NegationExpression implements Expression {

	public $expression;

	function __construct(Expression $expression) {
		$this->expression = $expression;
	}

	function __clone() {
		$this->expression = clone $this->expression;
	}
}
