<?php

namespace London\Run;

use London\Event\EventEmitter;

abstract class Response extends EventEmitter {
	use Receiver;

	public Context $context;

	public ?Result $result;

	public View $view;

	function __construct(Context $context, View $view) {
		$this->context = $context;
		$this->view = $view;
		$this->bubble($context, "response:");
	}

	function endBuffer(): string {
		return ob_get_clean();
	}

	function render() {
		return $this->view->render($this);
	}

	function setResult(Result $result) {
		$this->receive("result", $result);
	}

	function startBuffer() {
		return ob_start();
	}
}