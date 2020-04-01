<?php

namespace London\Parse;

class Terminal implements Rule {
	
	public $string;
	
	function __construct($string) {
		$this->string = $string;
	}
	
	function matches($input, $n, &$l, Stack $stack) {
		$l = strlen($this->string);
		$sub = substr($input, $n, $l);
		
		if ($sub === $this->string) {
			return $this->string;
		} else {
			return false;
		}
	}
	
}
