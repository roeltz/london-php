<?php

namespace London\Parse;

use Exception;

class SyntaxError extends Exception {
	
	private $string;
	
	private $offset;
	
	private $rule;
		
	function __construct($message, $string, $offset, Rule $rule = null) {
		$this->string = $string;
		$this->offset = $offset;
		$this->rule = $rule;
		[$line, $col] = $this->getLocation($string, $offset);
		$extract = substr($string, $offset, 20);
		parent::__construct("Syntax error at $line:$col, at '$extract...': $message");
	}
	
	function getLocation($string, $offset) {
		$line = substr_count($string, "\n", 0, $offset);
		$col = $offset - strrpos(substr($string, 0, $offset), "\n");
		return array($line + 1, $col);
	}
	
	function getOffset() {
		return $this->offset;
	}

	function getRule() {
		return $this->rule;
	}

	function getString() {
		return $this->string;
	}
}
