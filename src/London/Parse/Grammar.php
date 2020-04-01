<?php

namespace London\Parse;

class Grammar extends NonTerminal {
	
	const RECOVERABLE = 1;
	
	protected $grammar = [];
	
	protected $flags;
	
	function __construct(int $flags = 0) {
		$this->flags = $flags;
	}
	
	function add(string $name): self {
		$this->grammar[] = $name;
		return $this;
	}
	
	function matches(string $input, int $n, int &$l, Stack $stack) {
		$matches = [];
		$cursor = 0;
		$length = strlen($input);
		
		while ($cursor < $length) {
			$match = null;

			foreach ($this->grammar as $name) {
				$l = 0;
				$rule = $this->rules[$name];
				
				try {
					$match = $rule->matches($input, $cursor, $l, $stack);
					
					if ($match !== false) {
						$cursor += $l;
						$matches[] = $match;
					}
				} catch(SyntaxError $ex) {
					if ($this->isRecoverable()) {
						$cursor = $ex->getOffset();
					} else {
						throw $ex;
					}
				}
			}
			
			if (!$match) {
				if ($this->isRecoverable()) {
					$cursor++;
				} else {
					throw new SyntaxError("Unrecognizable input", $string, $cursor);
				}
			}
		}
		
		if ($this->production) {
			$matches = call_user_func($this->production, $matches, $stack, $input, $n, $l);
		}
		
		return $matches;
	}
	
	function isRecoverable(): bool {
		return $this->flags & self::RECOVERABLE;
	}
}