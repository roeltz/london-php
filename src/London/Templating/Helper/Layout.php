<?php

namespace London\Templating\Helper;

use London\Templating\Helper;
use London\Templating\HelperWithLifecycle;
use London\Util\Arrays;

class Layout extends Helper implements HelperWithLifecycle {

	protected $contentBuffers = [];

	protected $stack = [];

	function beginContent($name) {
		ob_start();
		$this->stack[0]->openBuffers[] = $name;
		return $this;
	}

	function buffer($name) {
		$this->beginContent($name);
		return $this;
	}

	function endContent($name) {
		$this->contentBuffers[$name] = ob_get_clean();
		Arrays::remove($this->stack[0]->openBuffers, $name);
		return $this;
	}

	function endHelperLifecycle() {
		if ($this->stack) {
			$entry = $this->stack[0];

			foreach ($entry->openBuffers as $name)
				$this->endContent($name);

			if ($entry->template)
				echo $this->renderWithCallingEngine([], ["view"=>$entry->template]);

			array_shift($this->stack);
		}
	}

	function placeholder($name) {
		echo @$this->contentBuffers[$name];
		return $this;
	}

	function put($name, $view) {
		$this->contentBuffers[$name] = $this->renderWithCallingEngine([], ["view"=>$view]);
		return $this;
	}

	function setContent($name, $content = null) {
		if ($content === null) {
			return @$this->contentBuffers[$name];
		} else {
			$this->contentBuffers[$name] = $content;
			return $this;
		}
	}

	function startHelperLifecycle() {
		array_unshift($this->stack, new LayoutStackEntry);
	}

	function template($name) {
		$this->stack[0]->template = $name;
		return $this;
	}
}

class LayoutStackEntry {

	public $openBuffers = [];

	public $template;
}
