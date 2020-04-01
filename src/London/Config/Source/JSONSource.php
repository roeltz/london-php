<?php

namespace London\Config\Source;

use London\Config\Source;

class JSONSource implements Source {

	function getConfigFromPath(string $path): array {
		if (file_exists($file = "$path.json")) {
			return json_decode(file_get_contents($file), JSON_OBJECT_AS_ARRAY);
		} else {
			return [];
		}
	}
}