<?php

namespace London\Locale {
	
	use London\Util\Strings;

	function translate(string $message, array $parameters = [], $domain = "default") {
		if ($locale = Locale::get()) {
			return Strings::fill($locale->translate($message, $domain), $parameters);
		} else {
			return Strings::fill($message, $parameters);
		}
	}
}

namespace {

	if (!function_exists("__")) {
		function __(...$args) {
			return London\Locale\translate(...$args);
		}
	}
}
