<?php

namespace London\Templating\Helper;

use London\Templating\Helper;

class PHP extends Helper {

	function __invoke($name = null, $data = null, $options = null) {
		if ($name) {
			$data = is_array($data) ? $data : $this->data;
			$options = is_array($options) ? $options : $this->options;

			return $this->put($name, $data, $options);
		} else {
			return $this->view();
		}
	}

	function inline($view) {
		$this->put($view, $this->data, $this->options);
	}

	function put($view, array $data = [], array $options = []) {
		$engine = clone $this->callingEngine;

		return $engine->render($data, array_merge($options, [
			'view'=>$view,
			'view-dir'=>dirname($this->callingFile)
		]));
	}

	function view() {
		$engine = clone $this->callingEngine;
		
		return $engine->render($this->data, [
			"view"=>$this->options["view"],
			"view-dir"=>$this->options["view-dir"]
		]);
	}
}