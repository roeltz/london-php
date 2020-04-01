<?php

namespace London\ORM;
use London\Cache\MemoryCache;
use London\Data\Query\Query;

class InstanceCache extends MemoryCache {

	function hash(Query $query) {
		return md5(serialize($query->expressions));
	}

	function getFromQuery(Query $query) {
		return $this->get($this->hash($query));
	}

	function setForQuery(Query $query, Entity $value) {
		return $this->set($this->hash($query), $value);
	}
}