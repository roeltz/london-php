<?php

namespace London\Annotation;

abstract class Annotation {

	public $value = true;

	final function __construct(array $values) {
		$values = $this->filter($values);

		foreach ($values as $property=>$value) {
			$this->$property = $value;
		}

		$this->init();
	}

	function filter(array $values): array {
		$vars = get_object_vars($this);
		
		foreach ($values as $property=>$value) {
			if (!array_key_exists($property, $vars)) {
				throw new AnnotationException("Invalid property '$property' in annotation ".get_class($this));
			}
		}
		
		return $values;
	}

	function init() {}
}