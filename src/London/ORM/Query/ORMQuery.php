<?php

namespace London\ORM\Query;

use London\Data\Query\Query;
use London\Data\Source\DataSource;
use London\ORM\Descriptor\ClassDescriptor;
use London\ORM\Exception\NotFoundException;
use London\ORM\Entity;
use London\ORM\ORM;

class ORMQuery extends Query {

	public $bring = [];

	public $descriptor;

	public $invoke = [];

	function __construct(DataSource $dataSource, ClassDescriptor $descriptor, ORMQuery $parent = null) {
		parent::__construct($dataSource, $parent);
		$this->descriptor = $descriptor;
		$this->from($dataSource->getCollection($descriptor->collection));
		$this->fields(...$descriptor->getPersistedFields(false));
	}

	function bring(string $property, ...$constraints) {
		$bring = Bring::fromParentQuery($this, $property);
		$this->bring[$property] = $bring;

		if (@$constraints[0] === true) {
			return $bring;
		} elseif ($constraints) {
			$bring->where($constraints);
			return $this;
		}
	}

	function getMappedQuery() {
		$mapper = ORM::getMapper($this->descriptor->class);
		return $mapper->map($this);
	}

	function invoke(string $method, ...$arguments) {
		$this->invoke[] = new Invocation($method, $arguments);
		return $this;
	}

	function getAll() {
		$query = $this->getMappedQuery();
		$result = $query->getAll();
		$result = $this->processResult($result);
		return $result;
	}

	function getSingleWithInstance(Entity $instance, bool $throw = true): Entity {
		$query = $this->getMappedQuery();

		if ($record = $query->getSingle()) {
			return $this->processItem($record, $instance);
		} elseif ($throw) {
			throw new NotFoundException(get_class($instance));
		}
	}

	function processItem(array $item, Entity $instance = null) {
		if (!$instance)
			$instance = new $this->descriptor->class;

		$instance->cast($item);

		foreach ($this->bring as $bring) {
			$bring->apply($instance);
		}

		foreach ($this->invoke as $invocation) {
			$invocation->apply($instance);
		}

		return $instance;
	}

	function processResult(array $result) {
		foreach ($result as &$item) {
			$item = $this->processItem($item);
		}

		return $result;
	}
}