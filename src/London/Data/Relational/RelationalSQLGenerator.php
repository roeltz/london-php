<?php

namespace London\Data\Relational;

use London\Data\Query\Query;
use London\Data\SQL\GenericSQLGenerator;

abstract class RelationalSQLGenerator extends GenericSQLGenerator {

	function generateSelectComponents(Query $query, array $fields = null) {
		$components = parent::generateSelectComponents($query, $fields);

		if ($query instanceof RelationalQuery) {
			if ($query->joins) {
				$this->insertComponent($components, "collection", [
					"join"=>$this->renderJoins($query->joins)
				]);
			}

			if ($query->groups) {
				$this->insertComponent($components, "where", [
					"group-by"=>$this->renderGroups($query->groups)
				]);
			}
		}

		return $components;
	}

	function renderJoins(array $joins) {
		return join(" ", array_map([$this, "renderJoin"], $joins));
	}

	function renderJoin(Join $join) {
		$type = strtoupper($join->type);
		$c = $this->renderCollection($join->collection);
		$a = $this->escapeField($join->a);
		$b = $this->escapeField($join->b);
		return "$type JOIN $c ON $a = $b";
	}
}
