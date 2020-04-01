<?php

namespace London\Parse;

class RuleReference implements Rule {
	
	protected $parent;
	
	protected $name;
	
	function __construct(NonTerminal $parent, $name) {
		$this->parent = $parent;
		$this->name = $name;
	}
	
	function matches(string $input, int $n, int &$l, Stack $stack) {
		$rule = $this->parent->lookup($this->name);
		
		if ($rule) {
			$match = $rule->matches($input, $n, $l, $stack);
			
			if ($match === false) {
				return false;
			} else {
				return $match;
			}
		} else {
			throw new SyntaxError("Referenced rule {$this->name} not found");
		}
	}
	
}
