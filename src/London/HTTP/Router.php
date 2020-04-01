<?php

namespace London\HTTP;

use London\Run\Action;
use London\Run\Exception\RoutingException;
use London\Run\Router as RouterInterface;
use London\Run\Request;
use London\Match\Matcher;
use London\Match\Pattern;

class Router implements RouterInterface {

	const EXPRESSION_PATTERN = '#(?:(HTTPS)\s+)?(?:(GET|POST|PUT|DELETE)\s+)?(?://([^/])+/)?(\S+)#i';

	protected $patterns = [];

	function __construct(array $options = null) {
		if ($options) {
			$this->route($options);
		}
	}

	function add(string $expression, $callable) {
		$this->patterns[] = $this->parse($expression, $callable);
		return $this;
	}

	function route(array $options) {
		$base = isset($options["base"]) ? $options["base"] : "/";
		$basePattern = $this->parse($base);

		foreach ($options["routes"] as $route=>$action) {
			$routePattern = $this->parse($route, $action);
			$actionPattern = $this->merge($basePattern, $routePattern);
			$this->patterns[] = $actionPattern;
		}
	}

	function merge(Pattern $a, Pattern $b) {
		$c = new Pattern($b->value);
		$c->state = array_merge($a->state, $b->state);
		$c->state["path"]["capture"] = $a->state["path"]["capture"] . $b->state["path"]["capture"];
		return $c;
	}

	function parse(string $expression, $callable = null) {
		if (preg_match(self::EXPRESSION_PATTERN, $expression, $m)) {
			$pattern = new Pattern($callable);
			$pattern
				->equals("method", $m[2] ? strtoupper($m[2]) : "GET")
				->capture("path", $m[4]);

			if (strtoupper($m[1]) == "HTTPS") {
				$pattern->equals("https", true);
			}

			if ($m[3]) {
				$pattern->capture("host", $m[3]);
			}

			return $pattern;
		} else {
			throw new Exception("Invalid routing expression: $expression");
		}
	}

	function resolve(Request $request): Action {
		$matcher = new Matcher($this->patterns);
		$state = $request->getComparableState();
		$extractedData = [];

		if ($matcher->match($state, $extractedData)) {
			$request->mergeData($extractedData);
			return new Action($request->context, $matcher->lastMatch);
		} else {
			throw new RoutingException("Action not found");
		}
	}
}