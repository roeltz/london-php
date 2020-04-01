<?php

namespace London\Container;

class Container {

	protected static $instances = [];

	protected $store = [];

	static function instance(string $name = "default"): self {
		if (!isset(self::$instances[$name])) {
			self::$instances[$name] = new self();
		}

		return self::$instances[$name];
	}

	function get(string $key, ...$args) {
		if (isset($this->store[$key])) {
			return $this->store[$key]->getValue(...$args);
		} else {
			return null;
		}
	}

	function set(string $key, $value, int $flags = 0, string $class = null) {
		$this->store[$key] = new Entry($value, $flags, $class);
	}

	function setConstructor(string $key, callable $constructor, string $class = null) {
		$this->set($key, $constructor, Entry::CONSTRUCTOR, $class);
	}

	function setSingleton(string $key, callable $constructor, string $class = null) {
		$this->set($key, $constructor, Entry::SINGLETON, $class);
	}
}