<?php

namespace London\HTTP;

use London\HTTP\Exception\ViewException;
use London\HTTP\View\Engine;
use London\Run\Request as BaseRequest;
use London\Run\Response as BaseResponse;
use London\Run\Result;
use London\Run\View as ViewInterface;

class View implements ViewInterface {

	protected $engines = [];

	function __construct(array $engines = []) {
		foreach ($engines as $name=>$conditions) {
			$engine = $conditions["engine"];
			unset($conditions["engine"]);
			$this->engine($name, $engine, $conditions);
		}
	}

	function addEngine($name, $engine, array $conditions = []) {
		$this->engines[$name] = [$engine, $conditions];
		return $this;
	}

	function checkEngineCondition(Request $request, array $conditions) {
		if ($conditions) {
			if ($accept = @$conditions["accept"]) {
				return preg_match("#{$accept}#i", @$request->headers["accept"]);
			} elseif ($ext = @$conditions["ext"]) {
				return preg_match("#\\.{$ext}\$#", $request->path);
			}
		} else {
			return true;
		}
	}

	function outputHeaders(Response $response) {
		header("{$response->context->protocol} {$response->status}");

		foreach ($response->headers as $header=>$value) {
			header("$header: $value");
		}
	}

	function render(BaseResponse $response) {
		$this->outputHeaders($response);

		if ($response->body === false) {
			return;
		} elseif ($response->body) {
			$body = $response->body;
		} elseif ($engine = $this->selectEngine($response->context->request, $response->result)) {
			if (is_string($engine)) {
				$engine = new $engine();
			}

			$response->emit("view:engine", $engine);
			$body = $engine->renderResponse($response);
		} else {
			$body = "";
		}

		echo $body;
	}

	function selectEngine(Request $request, Result $result) {
		if ($engine = @$result->options["view-engine"]) {
			return $this->engines[$engine][0];
		} else {
			foreach ($this->engines as $name=>[$engine, $conditions]) {
				if ($this->checkEngineCondition($request, $conditions)) {
					return $engine;
				}
			}
		}
	}
}