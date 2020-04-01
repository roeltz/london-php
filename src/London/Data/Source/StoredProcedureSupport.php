<?php

namespace London\Data\Source;

interface StoredProcedureSupport {

	function callProcedure(string $name, ...$arguments);
}