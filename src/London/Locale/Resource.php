<?php

namespace London\Locale;

use London\Util\Strings;

abstract class Resource {

	protected $path;

	abstract function getMessage($message, $localeCode);
	
	final function __construct($path) {
		$this->path = $path;
	}
	
	protected function getLocalizedPath($localeCode) {
		return Strings::interpolate($this->path, function($k) use($localeCode){
			return $k === "locale" ? $localeCode : $k;
		});
	}
}
