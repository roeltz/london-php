<?php

namespace London\Data\Relational;

use London\Data\Field;
use London\Data\Query\Aggregate;

class Group {

	public $field;

	public $aggregates = [];

	static function parseAggregates(array $aggregates) {
		$parsed = [];

		foreach ($aggregates as $alias=>$aggregate) {
			$parsed[] = new Aggregate($aggregate[0], Field::from($aggregate[1]), Field::from($alias));
		}

		return $parsed;
	}

	function __construct(Field $field, ...$aggregates) {
		$this->field = $field;

		foreach ($aggregates as $aggregate) {
			$this->addAggregate($aggregate);
		}
	}

	function addAggregate(Aggregate $aggregate) {
		$this->aggregates[] = $aggregate;
	}
}