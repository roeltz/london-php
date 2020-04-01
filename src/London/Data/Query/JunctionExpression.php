<?php

namespace London\Data\Query;

class JunctionExpression extends ExpressionBuilder implements Expression {

	const CONJUNCTION = "and";
	const DISJUNCTION = "or";

	public $expressions = [];

	public $operator;

	function __construct(string $operator, $parent = null) {
		parent::__construct($parent);
		$this->operator = $operator;
	}

	function __clone() {
		foreach ($this->expressions as &$expression) {
			$expression = clone $expression;
		}
	}

	function add(Expression $expression) {
		$this->expressions[] = $expression;
		return $this;
	}

	function addAll(array $expressions) {
		foreach ($expressions as $expression) {
			$this->add($expression);
		}
		return $this;
	}
}
