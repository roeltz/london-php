<?php

namespace London\Run;

class DirectRouter implements Router {

	protected $callable;

	function __construct($callable, array $data = []) {
		$this->callable = $callable;
		$this->data = $data;
	}

	function resolve(Request $request): Action {
		$request->mergeData($this->data);
		return new Action($request->context, $this->callable);
	}
}