<?php

namespace London\Data\Source;

interface TransactionalDataSource {

	function beginTransaction();

	function commitTransaction();

	function rollbackTransaction();
}
