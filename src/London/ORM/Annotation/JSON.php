<?php

namespace London\ORM\Annotation;

class JSON extends Transform {

	const RETURN_OBJECT = "object";
	const RETURN_ARRAY = "array";

	public $pretty = false;
	
	public $returnType = self::RETURN_OBJECT;
	
	public $value = "json";

	function toArgs(): array {
		return [
			"pretty"=>$this->pretty,
			"returnType"=>$this->returnType
		];
	}
}