<?php

namespace London\Match;

class Pattern {

	const VAR_PATTERN = '/\{([^}]+)\}/';

	const VAR_CAPTURE_PATTERN = '([\w.,:;-]+)';

	public $state = [];

	public $value;

	function __construct($value) {
		$this->value = $value;
	}

	function equals(string $property, $value): self {
		$this->state[$property]["equals"] = $value;
		return $this;
	}

	function regex(string $property, string $regex): self {
		$this->state[$property]["regex"] = $regex;
		return $this;
	}

	function capture(string $property, string $expression): self {
		$this->state[$property]["capture"] = $expression;
		return $this;
	}

	function any(string $property): self {
		$this->state[$property]["any"] = true;
		return $this;
	}

	function matches(array $state, array &$capturedData): bool {
		$preliminarData = [];
		$captureCache = [];

		foreach ($this->state as $property=>$pattern) {
			$stateValue = @$state[$property];

			if (isset($pattern["equals"])) {
				if ($pattern["equals"] !== $stateValue) {
					return false;
				}
			} elseif (isset($pattern["regex"])) {
				if (!preg_match($pattern["regex"], $stateValue)) {
					return false;
				}
			} elseif (isset($pattern["capture"])) {
				if (!$this->matchesCapture($pattern["capture"], $stateValue, $preliminarData, $property, $captureCache)) {
					return false;
				}
			} elseif (!isset($pattern["any"])) {
				return false;
			}
		}

		$capturedData = $preliminarData;
		return true;
	}

	function matchesCapture(string $expression, $value, array &$capturedData, string $property = null, array &$cache = []): bool {
		[$regex, $vars] = $this->parseCapture($expression, $property, $cache);

		if (preg_match($regex, $value, $m)) {
			if (count($vars)) {
				$capturedData = array_combine($vars, array_slice($m, 1));
			}

			return true;
		}

		return false;
	}

	function parseCapture(string $expression, string $property = null, array &$cache = []) {
		if ($property && isset($cache[$property])) {
			return $cache[$property];
		} else {
			$vars = [];
			$regex = preg_replace_callback(self::VAR_PATTERN, function($m) use(&$vars){
				@[$var, $regex] = explode(":", trim($m[1]), 2);
				$vars[] = $var;
				return $regex ? $regex : self::VAR_CAPTURE_PATTERN;
			}, $expression);

			$regex = "#^$regex$#";

			if ($property) {
				$cache[$property] = [$regex, $vars];
			}

			return [$regex, $vars];
		}
	}
}