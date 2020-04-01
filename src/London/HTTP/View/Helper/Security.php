<?php

namespace London\HTTP\View\Helper;

use London\HTTP\Context;
use London\Templating\Helper;

class Security extends Helper {

	protected Context $context;

	function __construct(Context $context) {
		$this->context = $context;
	}

	function isAllowed(...$roles) {
		$user = $this->context->request->session->getUser();
		return $this->context->security->isAllowed($user, $roles);
	}
}