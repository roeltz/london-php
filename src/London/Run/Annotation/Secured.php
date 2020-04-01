<?php

namespace London\Run\Annotation;

use London\Action\MetadataSource\ActionAnnotation;

class Secured extends ActionAnnotation {

	const NAME = "secured";

	public $name = self::NAME;
	
	public $value = "*";
}