<?php

namespace London\HTTP\Locale;
use London\Dispatch\Dispatch;
use London\Dispatch\LocaleExtractor;
use London\Locale\Locale;

class CookieLocaleExtractor implements LocaleExtractor {
	
	protected $cookieName;
	
	function __construct($cookieName = "locale") {
		$this->cookieName = $cookieName;
	}
	
	function getLocale(Dispatch $dispatch): ?Locale {
		if ($cookie = @$_COOKIE[$this->cookieName]) {
			if (in_array($cookie, Locale::accepted())) {
				return new Locale($cookie);
			}
		}
	}
}
