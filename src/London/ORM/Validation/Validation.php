<?php

namespace London\ORM\Validation;

interface Validation {

	function validate(ValidationInfo $info);
}