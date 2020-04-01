<?php

namespace London\Run;

use London\Event\EventEmitter;
use London\Locale\Locale;
use London\Match\Comparable;

class Request extends EventEmitter implements Comparable {
	use Receiver;

	public ?Action $action;

	public array $data;

	public Context $context;

	public Session $session;

	function __construct(Context $context, Session $session, array $data) {
		$this->context = $context;
		$this->session = $session;
		$this->data = $data;
		$this->bubble($context, "request:");
	}

	function getComparableState(): array {
		$state = [];
		$state["context"] = $this->context->name;

		foreach ($this->data as $k=>$v) {
			$state["data:$k"] = $v;
		}

		return $state;
	}

	function mergeData(array $data): void {
		$this->data = array_merge($this->data, $data);
	}

	function setAction(Action $action) {
		$this->receive("action", $action);
	}
}