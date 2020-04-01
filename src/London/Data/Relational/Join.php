<?php

namespace London\Data\Relational;

use London\Data\Collection;
use London\Data\Field;

class Join {

	const TYPE_INNER = "inner";

    const TYPE_LEFT = "left";

    const TYPE_RIGHT = "right";

	public $collection;

    public $a;

	public $b;

    public $type;

	function __clone() {
		$this->collection = clone $this->collection;
		$this->a = clone $this->a;
		$this->a = clone $this->a;
	}

	function __construct(Collection $collection, Field $a, Field $b, $type = self::TYPE_INNER) {
		$this->collection = $collection;
		$this->a = $a;
		$this->b = $b;
		$this->type = $type;
	}
}