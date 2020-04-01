<?php

namespace London\Data\Relational;

use London\Data\Collection;
use London\Data\Field;
use London\Data\Query\Query;

class RelationalQuery extends Query {

	public $groups = [];

	public $joins = [];

	function join($collection, $a, $b, $type = Join::TYPE_INNER) {
		$this->joins[] = new Join(Collection::from($collection), Field::from($a), Field::from($b), $type);
		return $this;
	}

	function groupBy($field, array $aggregates) {
		$this->groups[] = new Group(Field::from($field), ...Group::parseAggregates($aggregates));
		return $this;
	}
}
