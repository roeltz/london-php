<?php

namespace London\Parse;

class Stack {
	
	protected $levels = [];
	
	function push() {
		array_unshift($this->levels, []);
	}
	
	function pop() {
		array_shift($this->levels);
	}
	
	function get($name) {
		foreach ($this->levels as $stack) {
			if (isset($stack[$name])) {
				return $stack[$name];
			}
		}
	}
	
	function set($name, $value) {
		$this->levels[0][$name] = $value;
	}
}
