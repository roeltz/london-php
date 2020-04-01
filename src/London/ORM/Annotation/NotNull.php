<?php

namespace London\ORM\Annotation;

class NotNull extends Validation {

	public $value = "notnull";

	function toArgs(): array {
		return [];
	}
}