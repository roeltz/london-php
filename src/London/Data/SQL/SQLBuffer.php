<?php

namespace London\Data\SQL;

use London\Util\Strings;

class SQLBuffer {

	protected $dataSource;

	protected $buffer = [];

	protected $params = [];

	function __construct(SQLDataSource $dataSource) {
		$this->dataSource = $dataSource;
	}

	function __toString() {
		return join(" ", $this->buffer);
	}

	function append($sql, array $params = null) {
		$this->buffer[] = $sql;
		$this->appendParams($params);
		return $this;
	}

	function appendParams(array $params = null) {
		if ($params)
			$this->params = array_merge($this->params, $params);
	}

	function getAll() {
		$this->appendParams($params);
		return $this->dataSource->querySQL($this, $this->params);
	}

	function queryField(array $params = null) {
		$this->appendParams($params);
		return $this->dataSource->querySQLField($this, $this->params);
	}

	function querySingle(array $params = null) {
		$this->appendParams($params);
		return $this->dataSource->querySQLSingle($this, $this->params);
	}

	function queryValue(array $params = null) {
		$this->appendParams($params);
		return $this->dataSource->querySQLValue($this, $this->params);
	}

}
