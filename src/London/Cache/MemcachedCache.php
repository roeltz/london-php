<?php

namespace London\Cache;

use Memcache;

class MemcachedCache implements Cache {

	private $memcache;

	function __construct(string $host, string $port) {
		$this->memcache = new Memcache;
		$this->memcache->pconnect($host, $port);
	}

	function destroy() {
		
	}

	function get(string $key) {
		return $this->memcache->get($key);
	}

	function has(string $key): bool {
		return $this->memcache->get($key) !== null;
	}

	function remove(string $key) {
		return $this->memcache->delete($key);
	}

	function set(string $key, $value) {
		$this->setExpire($key, $value, 0);
	}

	function setExpire(string $key, $value, $expire) {
		return $this->memcache->set($key, $value, 0, $expire);
	}
}