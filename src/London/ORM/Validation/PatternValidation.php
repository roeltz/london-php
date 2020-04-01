<?php

namespace London\ORM\Validation;

use London\ORM\Exception\ValidationException;

class PatternValidation implements Validation {

	function validate(ValidationInfo $info) {
		$regex = $info->args["regex"];

		if (!preg_match($regex, $info->value)) {
			$class = get_class($info->instance);
			throw new ValidationException("Property {$class}::{$info->property} does not follow to the pattern '$regex'");
		}
	}
}