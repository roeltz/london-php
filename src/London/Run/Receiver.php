<?php

namespace London\Run;

trait Receiver {

	function __set($key, $value) {
		$this->receive($key, $value);
	}

	function receive($key, $value) {
		$this->$key = $value;
		$this->emit($key, $value);
	}
}