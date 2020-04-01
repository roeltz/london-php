<?php

namespace London\Data\Source;

use London\Data\Collection;

interface MultipleInsertionSupport {

	function saveMultiple(array $items, Collection $collection);
}