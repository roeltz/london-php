<?php

namespace London\HTTP;

use London\Run\Response as BaseReponse;
use London\Run\Result;

class Response extends BaseReponse {

	public $body;

	public $download = false;
	
	public $headers = [];
	
	public $overrides = [];

	public $redirect = false;
	
	public $status = 200;

	static function fromGlobals(Context $context) {
		$view = new View();
		return new self($context, $view);
	}

	function redirect(string $uri) {
		$this->override("redirect");
		$this->setHeader("Location", $uri);
		$this->body = false;
	}

	function redirectLocal(string $path = "") {
		$this->redirect($this->context->localURL($path));
	}

	function setBody(string $body, string $contentType = null) {
		if ($contentType) {
			$this->setHeader("Content-Type", $contentType);
		}
	}

	function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}

	protected function IsNotOverriden(string $key) {
		$isOverriden = in_array($key, $this->overrides);
		$this->overrides[] = $key;
		return !$isOverriden;
	}

	protected function override(string $key) {
		$this->overrides[] = $key;
	}
}