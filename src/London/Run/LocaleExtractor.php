<?php

namespace London\Run;

use London\Locale\Locale;

interface LocaleExtractor {

	function getLocale(Request $request): ?Locale;
}
