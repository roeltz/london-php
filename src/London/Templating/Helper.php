<?php

namespace London\Templating;

class Helper {

	/**
	 * @var array Data to be rendered by the current templating engine
	 */
	protected $data;

	/**
	 * @var array Options passed to the current templating engine
	 */
	protected $options;

	/**
	 * @var Engine
	 */
	 public $callingEngine;

	/**
	 * @var string If engine is file based, this will be the the currently processed file
	 */
	protected $callingFile;

	final function initHelper(&$data, array &$options, $callingEngine, $callingFile) {
		$this->data = $data;
		$this->options = $options;
		$this->callingEngine = $callingEngine;
		$this->callingFile = $callingFile;
	}

	function renderWithCallingEngine($data, array $options = []) {
		$data = array_merge($this->data, $data);
		$options = array_merge($this->options, $options);
		$engine = clone $this->callingEngine;
		return $engine->render($data, $options);
	}
}