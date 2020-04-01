<?php

namespace London\Data\Source;

use London\Data\Collection;
use London\Data\Query\Aggregate;
use London\Data\Query\Query;

interface DataSource {

	function aggregate(Aggregate $aggregate, Query $query);

	function count(Query $query);
	
	function delete(Query $query);

	function dropCollection(Collection $collection);

	function escapeValue($value): string;

	function getCollection(string $name): Collection;
	
	function getConnection();

	function newQuery(): Query;

	function query(Query $query);

	function save(array $values, Collection $collection, string $sequence = null);
}