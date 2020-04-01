<?php

namespace London\Locale;

use Exception;

use London\Util\Arrays;

class Locale {

	public $code;

	protected static $accepted;
	
	protected static $current;
	
	protected static $resources = [];
	
	protected static $resourceClasses = [];
	
	static function accepted(...$args) {
		if ($args) {
			self::$accepted = Arrays::flatten($args);
		} else {
			return self::$accepted;
		}
	}
	
	static function set(Locale $locale) {
		self::$current = $locale;
	}
	
	static function get() {
		return self::$current;
	}
	
	static function registerResourceClass(string $class, $validator) {
		self::$resourceClasses[$class] = $validator;
	}
	
	static function registerResource(Resource $resource, string $domain = "default") {
		self::$resources[$domain][] = $resource;
	}
	
	static function registerResourceFilename(string $filename, string $domain = "default") {
		foreach (self::$resourceClasses as $class=>$validator) {
			if ($validator($filename)) {
				return self::registerResource(new $class($filename), $domain);
			}
		}
		throw new Exception("Could not find suitable resource class for '$filename'");
	}
	
	function __construct(string $code) {
		$this->code = $code;
	}
	
	function setEnvironment() {
		setlocale(LC_ALL, $this->code);
		self::set($this);
	}

	function translate(string $message, string $domain = "default") {
		if (isset(self::$resources[$domain])) {
			foreach (self::$resources[$domain] as $resource) {
				if ($translation = $resource->getMessage($message, $this->code)) {
					return $translation;
				}
			}
		}

		return $message;
	}
}
