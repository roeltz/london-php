<?php

namespace London\Parse;

class Parser {
	
	protected $grammar;
	
	function __construct(Grammar $grammar) {
		$this->grammar = $grammar;
	}
		
	/**
	 * @throws SyntaxException
	 */
	function parse(string $input): array {
		$l = 0;
		$stack = new Stack();
		return $this->grammar->matches($input, 0, $l, $stack);
	}
}