<?php

namespace London\Data\SQL;

use London\Data\Collection;
use London\Data\Field;
use London\Data\Query\Aggregate;
use London\Data\Query\Query;
use London\Data\Query\ComparissionExpression;
use London\Data\Query\Expression;
use London\Data\Query\JunctionExpression;
use London\Data\Query\ListExpression;
use London\Data\Query\NegationExpression;
use London\Data\Query\Order;
use London\Data\Query\RangeExpression;
use London\Data\Query\SQLExpression;
use London\Data\Relational\Join;
use London\Data\Relational\RelationalQuery;
use London\Util\Strings;

abstract class GenericSQLGenerator {

	abstract function escapeField(Field $field);

	abstract function escapeIdentifier($name);

	abstract function escapeValue($value);

	function generateAggregateQuery(Aggregate $aggregate, Query $query) {
		return join(" ", $this->generateAggregateQueryComponents($aggregate, $query));
	}

	function generateAggregateQueryComponents(Aggregate $aggregate, Query $query) {
		$components = $this->generateSelectComponents($query);
		$components["fields"] = strtoupper($aggregate->operator)."(".$this->escapeField($aggregate->field).")";
		return $components;
	}

	function generateAggregateComponents(Aggregate $aggregate) {
		$components = [
			"operator"=>strtoupper($aggregate->operator),
			"open"=>"(",
			"field"=>$aggregate->field->name == "*" ? "*" : $this->escapeField($aggregate->field),
			"close"=>")"
		];

		if ($aggregate->alias) {
			$components["alias-as"] = " AS ";
			$components["alias-name"] = $this->escapeField($aggregate->alias);
		}

		return $components;
	}

	function generateCount(Query $query) {
		return join(" ", $this->generateCountComponents($query));
	}

	function generateCountComponents(Query $query) {
		$components = $this->generateSelectComponents($query);
		$components["fields"] = "COUNT(*)";
		return $components;
	}

	function generateDelete(Query $query) {
		return join(" ", $this->generateDeleteComponents($query));
	}

	function generateDeleteComponents(Query $query) {
		$components = ["keyword"=>"DELETE FROM"];
		$components["collection"] = $this->renderCollection($query->collection);

		if ($query->expressions) {
			$components["where"] = "WHERE ".$this->renderExpressions($query->expressions);
		}

		return $components;
	}

	function generateInsert(array $values, Collection $collection) {
		return join(" ", $this->generateInsertComponents($values, $collection));
	}

	function generateMultipleInsert(array $values, Collection $collection) {
		return join(" ", $this->generateMultipleInsertComponents($values, $collection));
	}

	function generateInsertComponents(array $values, Collection $collection) {
		$components = $this->generateInsertHeaderComponents(array_keys($values), $collection);
		$components["values"] = "VALUES ".$this->generateInsertValues($values);
		return $components;
	}

	function generateMultipleInsertComponents(array $values, Collection $collection) {
		$components = $this->generateInsertHeaderComponents(array_keys($values[0]), $collection);
		$valuesList = [];

		foreach ($values as $row) {
			$valuesList[] = $this->generateInsertValues($row);
		}

		$components["values"] = "VALUES ".join(", ", $valuesList);
		return $components;
	}

	function generateInsertHeaderComponents(array $fields, Collection $collection) {
		$components = ["keyword"=>"INSERT INTO"];
		$components["collection"] = $this->renderCollection($collection);
		$escapedFields = [];

		foreach ($fields as $field) {
			$escapedFields[] = $this->escapeField(Field::from($field));
		}

		$components["fields"] = "(".join(", ", $escapedFields).")";
		return $components;
	}

	function generateInsertValues(array $values) {
		$escapedValues = [];

		foreach ($values as $value) {
			$escapedValues[] = $this->escapeValue($value);
		}

		return "(".join(", ", $escapedValues).")";
	}

	function generateSelect(Query $query, array $fields = null) {
		return join(" ", array_filter($this->generateSelectComponents($query, $fields)));
	}

	function generateSelectComponents(Query $query, array $fields = null) {
		$components = ["keyword"=>"SELECT"];
		$fields = $this->generateFieldList($query);

		if ($fields) {
			if ($query->distinct) {
				$components["distinct"] = "DISTINCT";
			}

			$components["fields"] = join(", ", $fields);
		} else {
			$components["fields"] = "*";
		}

		$components["collection"] = "FROM ".$this->renderCollection($query->collection);

		if ($query->expressions && $where = $this->renderExpressions($query->expressions)) {
			$components["where"] = "WHERE $where";
		} else {
			$components["where"] = "";
		}

		if ($query->order)
			$components["order-by"] = $this->renderOrder($query->order);

		if ($query->limit)
			$components["limit"] = $this->renderLimit($query->limit, $query->offset);

		return $components;
	}

	function generateFieldList(Query $query) {
		$fields = [];

		if ($query->fields)
			$fields = array_map([$this, "escapeField"], $query->fields);

		if ($query instanceof RelationalQuery && $query->groups) {
			foreach ($query->groups as $group) {
				foreach ($group->aggregates as $aggregate) {
					$fields[] = join("", $this->generateAggregateComponents($aggregate));
				}
			}
		}

		return $fields;
	}

	function generateUpdate(array $values, Query $query) {
		return join(" ", $this->generateUpdateComponents($values, $query));
	}

	function generateUpdateComponents(array $values, Query $query) {
		$components = ["keyword"=>"UPDATE"];
		$components["collection"] = $this->renderCollection($query->collection);
		$vars = [];

		foreach ($values as $field=>$value) {
			$vars[] = $this->escapeField(Field::from($field))." = ".$this->escapeValue($value);
		}

		$components["set"] = "SET ".join(", ", $vars);

		if ($query->expressions) {
			$components["where"] = "WHERE ".$this->renderExpressions($query->expressions);
		}

		return $components;
	}

	function interpolateParameters(string $sql, array $parameters) {
		return Strings::interpolate($sql, function($key) use($parameters){
			if ($key[0] == ":") {
				$key = substr($key, 1);
				$key = isset($parameters[$key]) ? $parameters[$key] : $key;
				return $this->escapeIdentifier($key);
			} else {
				return $this->escapeValue(@$parameters[$key]);
			}
		});
	}

	function renderCollection(Collection $collection) {
		$rendered = $this->escapeIdentifier($collection->name);

		if ($collection->alias) {
			$rendered .= " AS ".$this->escapeIdentifier($collection->alias);
		}

		return $rendered;
	}

	function renderComparissionExpression(ComparissionExpression $expression) {
		$a = $this->escapeField($expression->a);
		$o = $expression->operator;
		$b = $expression->b instanceof Field ? $this->escapeField($expression->b) : $this->escapeValue($expression->b);

		if ($o == "=" && $b === null) {
			return "$a IS NULL";
		} elseif ($o == "<>" && $b === null) {
			return "$a IS NOT NULL";
		} elseif ($o == "like") {
			return $this->renderLike($a, $b);
		} elseif ($o == "regex") {
			return $this->renderRegex($a, $b);
		} else {
			return "$a $o $b";
		}
	}

	function renderExpression(Expression $expression) {
		if ($expression instanceof ComparissionExpression) {
			return $this->renderComparissionExpression($expression);
		} elseif ($expression instanceof ListExpression) {
			return $this->renderListExpression($expression);
		} elseif ($expression instanceof RangeExpression) {
			return $this->renderRangeExpression($expression);
		} elseif ($expression instanceof JunctionExpression) {
			return $this->renderJunctionExpression($expression);
		} elseif ($expression instanceof NegationExpression) {
			return $this->renderNegationExpression($expression);
		} elseif ($expression instanceof SQLExpression) {
			return $this->renderSQLExpression($expression);
		}
	}

	function renderExpressions(array $expressions) {
		if (count($expressions) == 1 && $expressions[0] instanceof JunctionExpression) {
			$expressions = $expressions[0];
		} else {
			$conjunction = new JunctionExpression(JunctionExpression::CONJUNCTION);
			$expressions = $conjunction->addAll($expressions);
		}

		return $this->renderExpression($expressions);
	}

	function renderJunctionExpression(JunctionExpression $expression) {
		$rendered = [];
		
		foreach ($expression->expressions as $e) {
			if ($e = $this->renderExpression($e)) {
				$rendered[] = $e;
			}
		}

		if ($rendered) {
			return "(".join(strtoupper(") {$expression->operator} ("), $rendered).")";
		}
	}

	function renderLike(string $a, string $b) {
		return "$a LIKE $b";
	}

	function renderListExpression(ListExpression $expression) {
		if ($expression->values) {
			$values = [];
			
			foreach ($expression->values as $v) {
				$values[] = $this->escapeValue($v);
			}
			
			$values = join(", ", $values);
			
			if ($values) {
				$field = $this->escapeField($expression->field);
				
				switch ($expression->operator) {
					case ListExpression::IN:
						return "$field IN ($values)";
					case ListExpression::NOT_IN:
						return "$field NOT IN ($values)";
				}
			}
		}
	}

	function renderLimit(int $length, int $offset) {
		if ($length == -1) {
			return "OFFSET {$offset}";
		} else {
			return "LIMIT {$length} OFFSET {$offset}";
		}
	}

	function renderNegationExpression(NegationExpression $expression) {
		return "NOT (".$this->renderExpression($expression).")";
	}

	function renderGroups(array $groups) {
		$rendered = [];
		
		foreach ($groups as $group) {
			$rendered[] = $this->escapeField($group->field);
		}

		return "GROUP BY ".join(", ", $rendered);
	}

	function renderOrder(array $order) {
		$rendered = [];

		foreach ($order as $o) {
			switch ($o->type) {
				case Order::ASC:
					$rendered[] = $this->escapeField($o->field)." ASC";
					break;
				case Order::DESC:
					$rendered[] = $this->escapeField($o->field)." DESC";
					break;
				case Order::RANDOM:
					$rendered[] = $this->getRandomOrderExpression();
					break;
				default:
					continue 2;
			}
		}

		return "ORDER BY ".join(", ", $rendered);
	}

	function renderRangeExpression(RangeExpression $expression) {
		$field = $this->escapeField($expression->field);
		$min = $this->escapeValue($expression->min);
		$max = $this->escapeValue($expression->max);
		return "$field BETWEEN $min AND $max";
	}

	function renderRegex($a, $b) {
		return "$a SIMILAR TO $b";
	}

	function renderSQLExpression(SQLExpression $expression) {
		if ($expression->parameters) {
			return Strings::fill($expression->sql, $expression->parameters);
		} else {
			return $expression->sql;
		}
	}

	function insertComponent(array &$components, string $key, array $new) {
		$index = array_search($key, array_keys($components)) + 1;
		$components = array_merge(
			array_slice($components, 0, $index),
			$new,
			array_slice($components, $index)
		);
	}
}
