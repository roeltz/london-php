<?php

namespace London\ORM\Query;

use London\ORM\Entity;
use London\ORM\ORM;
use London\ORM\Descriptor\ClassDescriptor;
use London\ORM\Descriptor\MultipleRelationDescriptor;

class Bring extends ORMQuery {

	public $property;

	private $mapped;

	static function fromEntityInstance(Entity $instance, string $property): self {
		return new Bring(ORM::getDescriptor(get_class($instance)), $property);
	}

	static function fromParentQuery(ORMQuery $parent, string $property): self {
		return new Bring($parent->descriptor, $property, $parent);
	}

	function __construct(ClassDescriptor $sourceDescriptor, string $property, ORMQuery $parent = null) {
		$descriptor = $sourceDescriptor->getRelatedClassDescriptor($property);
		parent::__construct(ORM::getDataSource($descriptor->class), $descriptor, $parent);
		$this->property = $property;
	}

	function apply(Entity $entity) {
		if (!$this->mapped) {
			$this->mapped = $this->getMappedQuery();
		}

		$copy = clone $this->mapped;

		$relationDescriptor = ORM::getDescriptor($entity)->getRelationDescriptor($this->property);
		$relationDescriptor->applyKeys($entity, $this->descriptor, $copy);

		if ($relationDescriptor instanceof MultipleRelationDescriptor) {
			$result = $copy->getAll();
			$result = $this->processResult($result);
		} else {
			$cache = ORM::getInstanceCache($this->descriptor->class);

			if ($cachedInstance = $cache->getFromQuery($copy)) {
				$result = $cachedInstance;
			} else {
				$result = $copy->getSingle();

				if ($result) {
					$result = $this->processItem($result);
				}

				$cache->setForQuery($copy, $result);
			}
		}

		$entity->{$this->property} = $result;

		return $result;
	}
}