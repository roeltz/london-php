<?php

namespace London\Container;

use LogicException;

class Entry {

	const CONSTRUCTOR = 1;
	const SINGLETON = 2;
	const LOCKED = 4;

	protected $flags;

	protected $initialized = false;

	protected $singleton;

	protected $value;

	protected $type;

	function __construct($value, $flags = 0, $type = null) {
		$this->value = $value;
		$this->flags = $flags;
		$this->type = $type;
	}

	function getValue(...$args) {
		if ($this->flags & self::CONSTRUCTOR) {
			return $this->invokeConstructor(...$args);
		} elseif ($this->flags & self::SINGLETON) {
			if ($this->singleton === null) {
				$this->singleton = $this->invokeConstructor(...$args);
			}
			return $this->singleton;
		} else {
			return $this->value;
		}
	}

	function lock() {
		$this->flags |= self::LOCKED;
		return $this;
	}

	function setValue($value) {
		if (!($this->flags & self::LOCKED) || !$this->initialized) {
			$this->value = $value;
			$this->initialized = true;
			return $this;
		} else {
			throw new LogicException("Registry entry is locked");
		}
	}

	protected function invokeConstructor(...$args) {
		if ($this->value) {
			$value = call_user_func($this->value, ...$args);

			if (!$this->type || $value instanceof $this->type) {
				return $value;
			} else {
				throw new LogicException("Container entry could not produce an instance of '{$this->type}'");
			}
		} else {
			throw new InvalidArgumentException("Container constructor entry has not been set a callable value");
		}
	}

}
