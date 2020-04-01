<?php

namespace London\Data\Query;

use London\Data\Field;

abstract class ExpressionBuilder {

	public $expressions = [];

	private $parent;

	function __construct($parent) {
		$this->parent = $parent;
	}

	function done() {
		return $this->parent;
	}

	function eq($a, $b) {
		$this->expressions[] = new ComparissionExpression(ComparissionExpression::EQ, new Field($a), $b);
		return $this;
	}

	function eqAll(array $values) {
		foreach ($values as $field=>$value) {
			$this->eq($field, $value);
		}
		
		return $this;
	}

	function ne($a, $b) {
		$this->expressions[] = new ComparissionExpression(ComparissionExpression::NE, new Field($a), $b);
		return $this;
	}

	function lt($a, $b) {
		$this->expressions[] = new ComparissionExpression(ComparissionExpression::LT, new Field($a), $b);
		return $this;
	}

	function le($a, $b) {
		$this->expressions[] = new ComparissionExpression(ComparissionExpression::LE, new Field($a), $b);
		return $this;
	}

	function gt($a, $b) {
		$this->expressions[] = new ComparissionExpression(ComparissionExpression::GT, new Field($a), $b);
		return $this;
	}

	function ge($a, $b) {
		$this->expressions[] = new ComparissionExpression(ComparissionExpression::GE, new Field($a), $b);
		return $this;
	}

	function like($a, $b) {
		$this->expressions[] = new ComparissionExpression(ComparissionExpression::LIKE, new Field($a), $b);
		return $this;
	}

	function ilike($a, $b) {
		$this->expressions[] = new ComparissionExpression(ComparissionExpression::ILIKE, new Field($a), $b);
		return $this;
	}

	function regex($a, string $b) {
		$this->expressions[] = new ComparissionExpression(ComparissionExpression::REGEX, new Field($a), $b);
		return $this;
	}

	function _and() {
		$junction = new JunctionExpression(JunctionExpression::CONJUNCTION, $this);
		$this->expressions[] = $junction;
		return $junction;
	}

	function _or() {
		$junction = new JunctionExpression(JunctionExpression::DISJUNCTION, $this);
		$this->expressions[] = $junction;
		return $junction;
	}

	function where() {
		return $this->_and();
	}
}