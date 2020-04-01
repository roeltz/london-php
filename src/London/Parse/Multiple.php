<?php

namespace London\Parse;

class Multiple extends NonTerminal {
	
	public $min;
	
	public $max;
	
	function __construct(NonTerminal $parent, $min = 0, $max = PHP_INT_MAX) {
		parent::__construct($parent);
		$this->min = $min;
		$this->max = $max;
	}
	
	function matches($input, $n, &$l, Stack $stack) {
		$l = 0;
		$matches = [];
		
		do {
			$stack->push();
			$ll = 0;
			$match = parent::matches($input, $n + $l, $ll, $stack);
			
			if ($match !== false) {
				$matches[] = $match;
				$l += $ll;
			}
			
			$stack->pop();
		} while ($match !== false);
		
		$count = count($matches);
		
		if ($count >= $this->min && $count <= $this->max) {
			
			if ($this->production) { 
				$matches = call_user_func($this->production, $matches, $stack, $input, $n, $l);
			}
			
			return $matches;
		} else {
			return false;
		}
	}
}