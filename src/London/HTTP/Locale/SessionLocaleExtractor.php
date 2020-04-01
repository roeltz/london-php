<?php

namespace London\HTTP\Locale;

use London\Locale\Locale;
use London\HTTP\Request;
use London\Run\LocaleExtractor;

class SessionLocaleExtractor implements LocaleExtractor {

	protected $callable;

	function __construct(callable $callable) {
		$this->callable = $callable;
	}

	function getLocale(Request $request): ?Locale {
		$result = call_user_func($this->callable, $request->session);

		if (!($result instanceof Locale)) {
			$result = new Locale($result);
		}

		return $result;
	}
}
