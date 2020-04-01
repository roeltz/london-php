<?php

namespace London\ORM\Descriptor;
use London\Data\Query\Query;
use London\ORM\Entity;
use London\ORM\ORM;

class RelationDescriptor {

	public $class;

	public $fk = [];
	
	public $property;

	function __construct(string $property, string $class, array $fk) {
		$this->property = $property;
		$this->class = $class;
		$this->fk = $fk ? $fk : [$property];
	}

	function applyKeys(Entity $entity, ClassDescriptor $descriptor, Query $query) {
		$pk = $descriptor->getPrimaryKeys();

		foreach ($this->fk as $i=>$key) {
			$value = $entity->getEntityRecordValue($key);
			$query->eq($pk[$i], $value);
		}
	}

	function isCompound() {
		return count($this->fk) > 1;
	}

	function isPersistent() {
		return true;
	}
}