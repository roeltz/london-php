<?php

namespace London\HTTP;

use London\Run\Session as BaseSession;

class Session extends BaseSession {

	public $id;

	function __construct(Context $context, $id = null) {
		parent::__construct($context);

		if ($id) {
			if ($id !== session_id()) {
				session_write_close();
			}
			
			$this->id = $id;
			session_id($id);
			@session_start();
		} else {
			$this->id = session_id();
		}
	}

	function get(string $key) {
		return @$_SESSION[$key];
	}

	function delete(string $key) {
		unset($_SESSION[$key]);
	}

	function destroy() {
		session_destroy();
	}

	function has(string $key): bool {
		return isset($_SESSION[$key]);
	}

	function set(string $key, $value) {
		$_SESSION[$key] = $value;
	}
}