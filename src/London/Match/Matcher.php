<?php

namespace London\Match;

class Matcher {

	public $lastMatch;
	
	protected $patterns;

	function __construct(array $patterns) {
		$this->patterns = $patterns;
	}

	function match(array $state, array &$capturedData) {
		$this->lastMatch = null;

		if ($state instanceof Comparable) {
			$state = $state->getComparableState();
		} elseif (is_object($state)) {
			$state = get_object_vars($state);
		}

		foreach ($this->patterns as $pattern) {
			if ($pattern->matches($state, $capturedData)) {
				$this->lastMatch = $pattern->value;
				return true;
			}
		}

		return false;
	}
}
