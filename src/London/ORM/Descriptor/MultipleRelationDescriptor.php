<?php

namespace London\ORM\Descriptor;

use London\Data\Field;
use London\Data\Query\ArrayExpressionParser;
use London\Data\Query\Order;
use London\Data\Query\Query;
use London\ORM\Descriptor\ClassDescriptor;
use London\ORM\Entity;
use London\ORM\ORM;

class MultipleRelationDescriptor extends RelationDescriptor {

	public $order;

	public $constraints;

	public $path;
 
	function __construct(string $property, string $class, array $fk, array $order, array $constraints, $path) {
		parent::__construct($property, $class, $fk);

		if ($order) {
			foreach ($order as $field=>$type) {
				$this->order[] = new Order(Field::from($field, $type));
			}
		}

		if ($constraints) {
			$this->constraints = ArrayExpressionParser::parse($constraints);
		}

		$this->path = $path;
	}

	function applyKeys(Entity $entity, ClassDescriptor $descriptor, Query $query) {
		$pk = ORM::getDescriptor($entity)->getPrimaryKeys();
		
		foreach ($this->fk as $i=>$key) {
			$value = $entity->getEntityRecordValue($pk[$i]);
			$query->eq($key, $value);
		}
	}

	function isPersistent() {
		return false;
	}
}