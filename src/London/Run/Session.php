<?php

namespace London\Run;

abstract class Session {

	const DEFAULT_USER_KEY = "london-session-user";
	
	const USER_KEY_CONFIG_PATH = "session.user.key";

	protected Context $context;

	protected $user;
	
	abstract function get(string $key);

	abstract function delete(string $key);

	abstract function destroy();

	abstract function has(string $key): bool;

	abstract function set(string $key, $value);
	
	function __construct(Context $context) {
		$this->context = $context;
	}

	function getUser() {
		if (!$this->user) {
			$this->user = $this->get($this->userKey());
		}

		return $this->user;
	}

	function setUser(User $user) {
		$this->user = $user;
		$this->set($this->userKey(), $user);
	}

	function unsetUser() {
		$this->user = null;
		$this->delete($this->userKey());
	}

	private function userKey() {
		return $this->context->config->get(self::USER_KEY_CONFIG_PATH) ?? self::DEFAULT_USER_KEY;
	}
}