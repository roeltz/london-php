<?php

namespace London\Config;

class Config {

	protected static $sources = [];
	
	protected $config = [];

	static function registerSource(Source $source) {
		self::$sources[] = $source;
	}

	function __construct(array $files = []) {
		foreach ($files as $file) {
			$this->load($file);
		}
	}
	
	function get(string $key, $default = null) {
		$value = $this->lookup($key);
		return $value ? $value : $default;
	}
	
	function set(string $key, $value): self {
		$key = &$this->lookup($key);
		$key = $value;
		return $this;
	}
	
	function load(string $path): self {
		foreach (self::$sources as $source) {
			$loadedConfig = $source->getConfigFromPath($path);

			if ($loadedConfig) {
				$this->config = array_merge_recursive($this->config, $loadedConfig);
				break;
			}
		}

		return $this;
	}
	
	protected function &lookup(string $key) {
		$currentLevel = &$this->config;
		$components = explode(".", $key);

		while ($components) {
			$property = array_shift($components);
			
			if (!isset($currentLevel[$property])) {
				$currentLevel[$property] = [];
			}

			$currentLevel = &$currentLevel[$property];
		}

		return $currentLevel;
	}
}
