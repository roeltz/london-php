<?php

namespace London\Parse;

class Optional extends NonTerminal {
	
	function matches($input, $n, &$l, Stack $stack) {
		$ll = 0;
		$match = parent::matches($input, $n, $ll, $stack);
		
		if ($match !== false) {
			$l = $ll;

			if ($this->production) {
				call_user_func($this->production, $match, $stack);
			}

			return $match;
		} else {
			if ($this->production) {
				$match = call_user_func($this->production, null, $stack, $input, $n, $l);
			}
			
			return $match;
		}
	}
}
