<?php

namespace London\Data\SQL;

use London\Data\Query\Query;

interface SQLDataSource {

	function escapeIdentifier(string $name);

	function executeSQL(string $sql, array $parameters = null);

	function getSQLBuffer();

	function querySQL(string $sql, array $parameters = null);

	function querySQLSingle(string $sql, array $parameters = null);

	function querySQLField(string $sql, array $parameters = null);

	function querySQLValue(string $sql, array $parameters = null);
}
