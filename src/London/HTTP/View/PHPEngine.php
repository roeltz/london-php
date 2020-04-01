<?php

namespace London\HTTP\View;

use London\HTTP\Context;
use London\HTTP\Response;
use London\HTTP\View\Helper;
use London\Run\Result;
use London\Run\User;
use London\Templating\PHPEngine as BasePHPEngine;

class PHPEngine extends BasePHPEngine implements Engine {

	function __construct(Context $context) {
		parent::__construct();
		$this->addHelper("context", $context);
		$this->addHelper("user", $context->request->session->getUser());
		$this->addHelper("security", new Helper\Security($context));
	}

	function renderResponse(Response $response): string {
		$response->setHeader("Content-Type", "text/html");
		return $this->render($response->result->data, $response->result->options);
	}
}