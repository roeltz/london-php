<?php

namespace London\HTTP\Locale;

use London\Locale\Locale;
use London\Run\LocaleExtractor;
use London\Run\Request;

class HeaderLocaleExtractor implements LocaleExtractor {
	
	function getLocale(Request $request): ?Locale {
		if ($header = @$request->headers['accept-language']) {
			preg_match_all($this->getRegex(), $header, $matches);
			
			if ($matches) {
				return new Locale($matches[0][0]);
			}
		}
	}
	
	private function getRegex() {
		$regex = join("|", Locale::accepted());
		return "/$regex/i";
	}
}
