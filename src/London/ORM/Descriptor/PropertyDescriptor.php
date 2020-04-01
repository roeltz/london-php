<?php

namespace London\ORM\Descriptor;

class PropertyDescriptor {

	public $cascaded = false;

	public $computedBy;

	public $eager = false;

	public $embedded = false;

	public $generated = false;

	public $name;

	public $orderByDefault;

	public $persistent = true;

	public $pk;

	public $underlyingName;

	public $transformations = [];

	public $validations = [];

	function __construct(string $name) {
		$this->name = $name;
		$this->underlyingName = [$name];
	}
}