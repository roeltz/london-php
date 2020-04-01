<?php

namespace London\ORM\Exception;

class NotFoundException extends ORMException {

	function __construct($class) {
		parent::__construct("Entity of class '$class' not found");
	}
}