<?php

namespace London\ORM\Query;

use London\ORM\Entity;

class Invocation {

	public $args;

	public $method;

	function __construct(string $method, array $args = []) {
		$this->method = $method;
		$this->args = $args;
	}

	function apply(Entity $entity) {
		call_user_func_array([$entity, $this->method], $this->args);
	}
}