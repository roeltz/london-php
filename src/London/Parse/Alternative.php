<?php

namespace London\Parse;

class Alternative extends NonTerminal {
	
	function matches(string $input, int $n, int &$l, Stack $stack) {
		$ll = 0;
		
		foreach ($this->rules as $name=>$rule) {
			$stack->push();
			$match = $rule->matches($input, $n, $ll, $stack);
			
			if ($match !== false) {
				$l = $ll;
				$stack->set($name, $match);
				
				if ($this->production) {
					$match = call_user_func($this->production, $match, $name, $stack, $input, $n, $l);
				}
				
				$stack->pop();
				return $match;
			}

			$stack->pop();
		}

		return false;
	}	
}