<?php

namespace London\Parse;

class Regex implements Rule {
	
	public $pattern;
	
	function __construct($pattern, $flags = null) {
		$this->pattern = "#^$pattern#$flags";
	}
	
	function matches($input, $n, &$l, Stack $stack) {
		if (preg_match($this->pattern, substr($input, $n), $m)) {
			$l = strlen($m[0]);
			return $m[0];
		} else {
			return false;
		}
	}
	
}
