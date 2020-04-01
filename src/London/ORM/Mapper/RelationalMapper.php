<?php

namespace London\ORM\Mapper;

use London\Data\Field;
use London\Data\Query\ComparissionExpression;
use London\Data\Query\Expression;
use London\Data\Query\JunctionExpression;
use London\Data\Query\ListExpression;
use London\Data\Query\NegationExpression;
use London\Data\Query\RangeExpression;
use London\Data\Relational\RelationalQuery;
use London\ORM\Descriptor\ClassDescriptor;
use London\ORM\Descriptor\RelationDescriptor;
use London\ORM\Descriptor\MultipleRelationDescriptor;
use London\ORM\ORM;
use London\ORM\Query\ORMQuery;
use London\Util\Objects;

class RelationalMapper implements Mapper {

	const PROPERTY_PATH_DELIMITER = ".";

	private $collectionCache = [];

	private $descriptor;

	private $mapped;

	function map(ORMQuery $source) {
		$this->descriptor = $source->descriptor;
		$this->mapped = $source->dataSource->newQuery();

		$this->mapped->from($this->descriptor->collection);
		$this->mapped->distinct($source->distinct);

		foreach ($source->fields as $field) {
			$this->mapped->fields($this->mapField($field));
		}

		foreach ($source->expressions as $expression) {
			$this->mapped->expressions[] = $this->mapExpression($expression);
		}

		foreach ($source->order as $order) {
			$this->mapped->orderBy($this->mapField($order->field), $order->type);
		}

		$this->mapped->limit($source->limit, $source->offset);

		return $this->mapped;
	}

	function getCollection(ClassDescriptor $descriptor, array $previous) {
		if ($previous) {
			$key = join(">>", array_map(function($p){ return "{$p[1]->class}::{$p[0]}"; }, $previous));
			$collection = @$this->collectionCache[$key];

			if (!$collection) {
				$collection = ORM::getDataSource($descriptor->class)->getCollection($descriptor->collection);
				$this->collectionCache[$key] = $collection;
				list($lastProperty, $lastDescriptor) = array_pop($previous);
				$lastCollection = $this->getCollection($lastDescriptor, $previous);
				$relation = $lastDescriptor->getRelationDescriptor($lastProperty);

				if ($relation instanceof MultipleRelationDescriptor) {
					$this->mapped->join(
						$collection,
						new Field($relation->fk[0], $collection),
						new Field($lastDescriptor->getPrimaryKeys(false)[0], $lastCollection)
					);
				} else {
					$this->mapped->join(
						$collection,
						new Field($relation->fk[0], $lastCollection),
						new Field($descriptor->getPrimaryKeys(false)[0], $collection)
					);
				}
			}
			return $collection;
		} else {
			return $this->mapped->collection;
		}
	}

	function getFieldByProperty(string $property, ClassDescriptor $descriptor, array $previous) {
		$underlyingName = $descriptor->getPropertyDescriptor($property)->underlyingName[0];
		$collection = $this->getCollection($descriptor, $previous);
		return new Field($underlyingName, $collection);
	}

	function resolveFieldByPath(array $path, ClassDescriptor $descriptor, array $previous = []) {
		$property = $path[0];

		if (count($path) == 1) {
			return $this->getFieldByProperty($property, $descriptor, $previous);
		} else if ($descriptor->getPropertyDescriptor($property)->embedded) {
			return join(self::PROPERTY_PATH_DELIMITER, $path);
		} else {
			$previous[] = [$property, $descriptor];
			$nextPath = array_slice($path, 1);
			$nextDescriptor = ORM::getDescriptor($descriptor->getRelationDescriptor($property)->class);
			return $this->resolveFieldByPath($nextPath, $nextDescriptor, $previous);
		}
	}

	function mapField(Field $field) {
		return $this->resolveFieldByPath(explode(self::PROPERTY_PATH_DELIMITER, $field->name), $this->descriptor);
	}

	function mapExpression(Expression $expression) {
		if ($expression instanceof ComparissionExpression) {
			return $this->mapComparissionExpression($expression);
		} elseif ($expression instanceof ListExpression || $expression instanceof RangeExpression) {
			return $this->mapExpressionWithField($expression);
		} elseif ($expression instanceof JunctionExpression) {
			return $this->mapJunctionExpression($expression);
		} elseif ($expression instanceof NegationExpression) {
			return $this->mapNegationExpression($expression);
		} else {
			return $expression;
		}
	}

	function mapComparissionExpression(ComparissionExpression $expression) {
		$copy = clone $expression;

		if ($copy->a instanceof Field) {
			$copy->a = $this->mapField($copy->a);
		}

		if ($copy->b instanceof Field) {
			$copy->b = $this->mapField($copy->b);
		}

		return $copy;
	}

	function mapExpressionWithField(Expression $expression) {
		$copy = clone $expression;
		$copy->field = $this->mapField($copy->field);
		return $copy;
	}

	function mapJunctionExpression(JunctionExpression $expression) {
		$copy = clone $expression;

		foreach ($copy->expressions as &$expression) {
			$expression = $this->mapExpression($expression);
		}

		return $copy;
	}

	function mapNegationExpression(NegationExpression $expression) {
		$copy = clone $expression;
		$copy->expression = $this->mapExpression($copy->expression);
		return $copy;
	}
}