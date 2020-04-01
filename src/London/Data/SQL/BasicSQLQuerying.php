<?php

namespace London\Data\SQL;

trait BasicSQLQuerying {

	function querySQLSingle(string $sql, array $parameters = null) {
		$result = $this->querySQL($sql, $parameters);
		return @$result[0];
	}

	function querySQLField(string $sql, array $parameters = null) {
		$result = $this->querySQL($sql, $parameters);
		
		foreach($result as &$record) {
			$record = reset($record);
		}
		
		return $result;
	}

	function querySQLValue(string $sql, array $parameters = null) {
		$result = $this->querySQLSingle($sql, $parameters);
		
		if ($result) {
			return reset($result);
		}
	}
}