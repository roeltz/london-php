<?php

namespace London\ORM\Validation;

use London\ORM\Exception\ValidationException;

class NotNullValidation implements Validation {

	function validate(ValidationInfo $info) {
		if (is_null($info->value)) {
			$class = get_class($info->instance);
			throw new ValidationException("Property {$class}::{$info->property} is null");
		}
	}
}