<?php

namespace London\ORM;

use London\ORM\Query\Bring;
use London\ORM\Query\ORMQuery;
use London\Util\Arrays;

class Entity {

	protected $record;

	static function newQuery(): ORMQuery {
		return ORM::newQuery(static::class);
	}

	function __construct(...$pk) {
		if ($pk) {
			$this->retrieve(...$pk);
		}
	}

	function __get($property) {
		$descriptor = ORM::getDescriptor($this);

		if ($descriptor->hasRelation($property)) {
			return $this->bring($property);
		}
	}

	function bring(string $property, bool $returnQuery = false) {
		$query = Bring::fromEntityInstance($this, $property);

		if ($returnQuery) {
			return $query;
		} else {
			return $query->apply($this);
		}
	}


	function cast(array $record) {
		$this->record = $record;
		ORM::cast($this, $record);
	}

	function delete(): bool {

	}

	function getEntityRecord(): array {
		return $this->record;
	}

	function getEntityRecordValue(string $key) {
		return $this->record[$key];
	}

	function refresh() {
		return $this->retrieve(ORM::getKeyValues($this));
	}

	function retrieve(...$pk) {
		if (!Arrays::isAssoc($pk)) {
			$pk = ORM::buildPK(ORM::getDescriptor($this), $pk);
		}

		$query = $this->newQuery()->eqAll($pk);
		$query->getSingleWithInstance($this);
		$cache = ORM::getInstanceCache(get_class($this));
		$cache->setForQuery($query, $this);

		return $this;
	}

	function save(): bool {
		$generatedKey = ORM::getMapper($this)->save($this);

		if ($generatedKey) {
			$descriptor = ORM::getDescriptor($this);
			$pk = $descriptor->getGeneratedProperty()->name;
			$this->{$pk} = $generatedKey;
		}

		$this->retrieve();
	}

	function update(): bool {

	}
}