<?php

namespace London\Parse;

class NonTerminal implements Rule {
	
	protected $parent;
	
	protected $production;
	
	protected $rules = [];
	
	protected $stack = [];
	
	function __construct(NonTerminal $parent) {
		$this->parent = $parent;
	}
	
	function end(callable $production = null) {
		if ($production) {
			$this->production = $production;
		}

		return $this->parent;
	}

	function get($name) {
		if (isset($this->rules[$name])) {
			return $this->rules[$name];
		} elseif ($this->parent) {
			return $this->parent->get($name);
		} else {
			return null;
		}
	}
	
	function lookup($name) {
		if ($this->parent) {
			return $this->parent->get($name);
		} else {
			return null;
		}
	}
	
	function last($name, $match = null) {
		if ($match) {
			$this->stack[$name] = $match;
		} elseif (isset($this->stack[$name])) {
			return $this->stack[$name];
		} elseif ($this->parent) {
			return $this->parent->last($name);
		}
	}
	
	function matches(string $input, int $n, int &$l, Stack $stack) {
		$l = 0;
		$matches = [];
		$stack->push();
		
		foreach ($this->rules as $name=>$rule) {
			$ll = 0;
			$match = $rule->matches($input, $n + $l, $ll, $stack);
			
			if ($match === false) {
				$stack->pop();
				return false;
			} else {
				$matches[$name] = $match;
				$stack->set($name, $match);
				$l += $ll;
			}
		}
		
		if ($this->production) {
			$matches = call_user_func($this->production, $matches, $stack, $input, $n, $l);
		}
		
		$stack->pop();
		return $matches;
	}

	function alternative($name) {
		return $this->rules[$name] = new Alternative($this);
	}
	
	function multiple($name, $min = 0, $max = PHP_INT_MAX) {
		return $this->rules[$name] = new Multiple($this, $min, $max);
	}
	
	function nonterminal($name) {
		return $this->rules[$name] = new NonTerminal($this);
	}

	function optional($name) {
		return $this->rules[$name] = new Optional($this);
	}
	
	function optionalRule($name, $defaultValue = null) {
		$opt = new Optional($this->parent);
		$opt->rule($name);
		$opt->end(function($m) use($name, $defaultValue){
			return $m === false ? $defaultValue : ($m[$name] ?? null);
		});
		$this->rules[$name] = $opt;
		
		return $this;
	}
	
	function regex($name, $pattern, $flags = null) {
		$this->rules[$name] = new Regex($pattern, $flags);
		return $this;
	}
	
	function rule($name, $ref = null) {
		if (!$ref) {
			$ref = $name;
		}

		$this->rules[$name] = new RuleReference($this, $ref);
		return $this;
	}

	function terminal($name, $literal) {
		$this->rules[$name] = new Terminal($literal);
		return $this;
	}
}
