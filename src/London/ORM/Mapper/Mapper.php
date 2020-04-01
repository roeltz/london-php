<?php

namespace London\ORM\Mapper;

use London\ORM\Query\ORMQuery;

interface Mapper {

	function map(ORMQuery $source);
}