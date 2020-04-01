<?php

namespace London\HTTP\Locale;

use London\Locale\Locale;
use London\Run\LocaleExtractor;
use London\Run\Request;

class URILocaleExtractor implements LocaleExtractor {
	
	const MODE_SUBDOMAIN = "subdomain";
	const MODE_PATH = "path";
	
	protected $mode;
	
	function __construct(string $mode) {
		$this->mode = $mode;
	}
		
	function getLocale(Request $request): ?Locale {
		preg_match_all($this->getRegex(), $request->uri, $matches);

		if (@$matches[1][0]) {
			return new Locale($matches[1][0]);
		}
	}
	
	private function getRegex() {
		$regex = join("|", Locale::accepted());

		switch ($this->mode) {
			case self::MODE_SUBDOMAIN:
				$regex = "//($regex)\\.";
				break;
			case self::MODE_PATH:
				$regex = "/($regex)/";
				break;
		}

		return "#$regex#";
	}
}
