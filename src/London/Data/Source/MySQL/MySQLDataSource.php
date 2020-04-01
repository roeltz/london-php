<?php

namespace London\Data\Source\MySQL;

use DateTime;
use mysqli;
use mysqli_result;
use London\Data\Collection;
use London\Data\Exception\AuthException;
use London\Data\Exception\ConstraintException;
use London\Data\Exception\DataException;
use London\Data\Exception\DuplicateEntryException;
use London\Data\Exception\QuerySyntaxException;
use London\Data\Exception\UnknownFieldException;
use London\Data\Exception\UnknownHostException;
use London\Data\Exception\UnknownSchemaException;
use London\Data\Query\Aggregate;
use London\Data\Query\Query;
use London\Data\Relational\RelationalQuery;
use London\Data\Source\DataSource;
use London\Data\Source\MultipleInsertionSupport;
use London\Data\Source\StoredProcedureSupport;
use London\Data\Source\TransactionalDataSource;
use London\Data\SQL\BasicSQLQuerying;
use London\Data\SQL\SQLBuffer;
use London\Data\SQL\SQLDataSource;

class MySQLDataSource implements DataSource, SQLDataSource, TransactionalDataSource,
	MultipleInsertionSupport, StoredProcedureSupport {
	use BasicSQLQuerying;
	
	const TYPE_TINYINT = 1;
	const TYPE_SMALLINT = 2;
	const TYPE_MEDIUMINT = 9;
	const TYPE_INT = 3;
	const TYPE_BIGINT = 8;
	const TYPE_DECIMAL = 246;
	const TYPE_FLOAT = 4;
	const TYPE_DOUBLE = 5;
	const TYPE_BIT = 16;
	const TYPE_DATE = 10;
	const TYPE_DATETIME = 12;
	const TYPE_TIMESTAMP = 7;
	const TYPE_TIME = 11;
	const TYPE_YEAR = 13;
	const TYPE_CHAR = 254;
	const TYPE_VARCHAR = 253;
	const TYPE_TEXT = 252;
	
	protected $connection;

	protected $generator;

	protected $logger;

	function __construct(string $db, string $host, string $user, string $password, array $options = []) {
		$this->connection = @new mysqli("p:$host", $user, $password, $db);

		if (!$this->connection->connect_errno) {
			$this->generator = new MySQLGenerator($this);
			$this->connection->set_charset("utf8");
			$this->connection->autocommit(true);
		} else {
			throw $this->translateException($this->connection->connect_errno, $this->connection->connect_error);
		}
	}

	function aggregate(Aggregate $aggregate, Query $query) {
		$sql = $this->generator->generateAggregateQuery($aggregate, $query);
		return $this->querySQLValue($sql);
	}

	function beginTransaction() {
		$this->executeSQL("START TRANSACTION");
	}

	function callProcedure(string $name, ...$arguments) {
		$sql = $this->generator->generateProcedureCall($name, $arguments);
		return $this->querySQL($sql);
	}

	function commitTransaction() {
		$this->executeSQL("COMMIT");
	}

	function count(Query $query) {
		$result = $this->querySQL($this->generator->generateCount($query));
		return current(current($result));
	}

	function delete(Query $query) {
		return $this->executeSQL($this->generator->generateDelete($query));
	}

	function dropCollection(Collection $collection) {
		return $this->executeSQL($this->generator->generateDrop($collection));
	}

	function escapeIdentifier(string $name): string {
		return $this->generator->escapeIdentifier($name);
	}

	function escapeValue($value): string {
		return $this->generator->escapeValue($value);
	}

	function executeSQL(string $sql, array $parameters = null) {
		if ($parameters)
			$sql = $this->generator->interpolateParameters($sql, $parameters);

		if ($this->logger) {
			$this->logger->debug($sql);
			$startTime = microtime(true);
		}

		if ($this->connection->query($sql)) {
			if ($this->logger) {
				$elapsedTime = microtime(true) - $startTime;
				$this->logger->debug("{$this->connection->affected_rows} affected row(s), took {$elapsedTime}s");
			}
			return $this->affected_rows;
		} else {
			throw $this->translateException($this->connection->errno, $this->connection->error);
		}
	}

	function getCollection(string $name): Collection {
		return new Collection($name);
	}

	function getConnection() {
		return $this->connection;
	}

	function getSQLBuffer() {
		return new SQLBuffer($this);
	}

	function newQuery(): Query {
		return new RelationalQuery($this);
	}

	function query(Query $query) {
		return $this->querySQL($this->generator->generateSelect($query));
	}

	function querySQL(string $sql, array $parameters = null) {
		if ($parameters) {
			$sql = $this->generator->interpolateParameters($sql, $parameters);
		}

		echo "Query: $sql\n";

		if ($this->logger) {
			$this->logger->debug($sql);
			$startTime = microtime(true);
		}

		$result = $this->connection->query($sql);

		if ($result) {
			$items = $this->processResult($result);
			echo "Returned ".count($items)." item(s)\n";

			if ($this->logger) {
				$elapsedTime = microtime(true) - $startTime;
				$count = count($items);
				$this->logger->debug("Query returned {$count} item(s)m took {$elapsedTime}s");
			}

			return $items;
		} else {
			throw $this->translateException($this->connection->errno, $this->connection->error);
		}
	}

	function rollbackTransaction() {
		$this->executeSQL("ROLLBACK");
	}

	function save(array $values, Collection $collection, $sequence = null) {
		$this->executeSQL($this->generator->generateInsert($values, $collection));
		return $this->connection->insert_id;
	}

	function saveMultiple(array $values, Collection $collection) {
		$this->executeSQL($this->generator->generateMultipleInsert($values, $collection));
		return $this->connection->insert_id;
	}

	function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	function update(array $values, Query $query) {
		return $this->executeSQL($this->generator->generateUpdate($values, $query));
	}

	protected function getResultTypes(mysqli_result $result) {
		$types = [];
		foreach ($result->fetch_fields() as $meta)
			$types[$meta->name] = $meta->type;
		return $types;
	}

	protected function processResult(mysqli_result $result) {
		$types = $this->getResultTypes($result);
		$items = [];

		while ($item = $result->fetch_assoc()) {
			$this->processResultItem($item, $types);
			$items[] = $item;
		}

		return $items;
	}

	protected function processResultItem(array &$items, array &$types) {
		foreach ($items as $field=>&$value) {
			if (!is_null($value)) {
				switch ($types[$field]) {
					case self::TYPE_TINYINT:
					case self::TYPE_SMALLINT:
					case self::TYPE_MEDIUMINT:
					case self::TYPE_INT:
					case self::TYPE_BIGINT:
					case self::TYPE_YEAR:
						$value = (int) $value;
						continue 2;
					case self::TYPE_DOUBLE:
					case self::TYPE_FLOAT:
					case self::TYPE_DECIMAL:
						$value = (double) $value;
						continue 2;
					case self::TYPE_DATE:
					case self::TYPE_DATETIME:
					case self::TYPE_TIMESTAMP:
						$value = new DateTime($value);
						continue 2;
					case self::TYPE_TIME:
						$value = new DateTime("1970-01-01 $value");
						continue 2;
				}
			}
		}
	}

	protected function translateException($code, $message) {
		if ($this->logger) {
			$this->logger->error($message);
		}

		switch ($code) {
			case 1044:
			case 1045:
				return new AuthException($message, $code);
			case 1049:
				return new UnknownSchemaException($message, $code);
			case 1054:
				return new UnknownFieldException($message, $code);
			case 1062:
				return new DuplicateEntryException($message, $code);
			case 1064:
				return new QuerySyntaxException($message, $code);
			case 1452:
				return new ConstraintException($message, $code);
			case 2002:
				return new UnknownHostException($message, $code);
			default:
				return new DataException($message, $code);
		}
	}
}