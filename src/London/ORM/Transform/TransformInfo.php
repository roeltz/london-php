<?php

namespace London\ORM\Transform;

use London\ORM\Entity;

class TransformInfo {

	public array $args;

	public Entity $instance;
	
	public string $property;
	
	public $value;

	function __construct(array $args, Entity $instance, string $property, $value) {
		$this->args = $args;
		$this->instance = $instance;
		$this->property = $property;
		$this->value = $value;
	}
}