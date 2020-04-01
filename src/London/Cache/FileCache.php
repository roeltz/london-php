<?php

namespace London\Cache;

class FileCache extends MemoryCache {

	protected $path;

	function __construct(string $path) {
		$this->path = $path;
	}

	function destroy() {
		foreach(glob($this->getFilename("*")) as $filename) {
			unlink($filename);
		}

		return parent::destroy();
	}

	function get(string $key) {
		if (parent::has($key)) {
			return parent::get($key);
		} elseif ($this->hasInFilesystem($key)) {
			parent::set($key, $this->retrieve($this->getFilename($key)));
			return parent::get($key);
		}
	}

	function has(string $key): bool {
		return parent::has($key) || $this->hasInFilesystem($key);
	}

	function remove(string $key) {
		unlink($this->getFilename($key));
		return parent::remove($key);
	}

	function set(string $key, $value) {
		$this->save($this->getFilename($key), $value);
		return parent::set($key, $value);
	}

	protected function getFilename(string $key) {
		return "{$this->path}/{$key}";
	}

	protected function hasInFilesystem(string $key) {
		return file_exists($this->getFilename($key));
	}

	protected function retrieve(string $filename) {
		return unserialize(file_get_contents($filename));
	}

	protected function save(string $filename, $value) {
		file_put_contents($filename, serialize($value));
	}
}