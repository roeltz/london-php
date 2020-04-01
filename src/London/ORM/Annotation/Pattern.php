<?php

namespace London\ORM\Annotation;

class Pattern extends Validation {

	public $regex;

	public $value = "pattern";

	function toArgs(): array {
		return [
			"regex"=>$this->regex
		];
	}
}