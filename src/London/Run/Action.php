<?php

namespace London\Run;

use London\Action\Action as BaseAction;

class Action extends BaseAction {

	public Context $context;

	function __construct(Context $context, $callable) {
		parent::__construct($callable);
		$this->context = $context;
	}

	function invokeResult(): Result {
		$data = $this->invoke($this->context->request->data, $this->context->toActionContext());
		return Result::from($data, $this->getMetadata());
	}
}