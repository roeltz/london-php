<?php

namespace London\Cache;

class MemoryCache implements Cache {

	protected $cache = array();

	function destroy() {
		unset($this->cache);
		$this->cache = array();
	}

	function get(string $key) {
		return @$this->cache[$key];
	}

	function has(string $key): bool {
		return isset($this->cache[$key]);
	}

	function remove(string $key) {
		unset($this->cache[$key]);
	}

	function set(string $key, $value) {
		$this->cache[$key] = $value;
	}
}