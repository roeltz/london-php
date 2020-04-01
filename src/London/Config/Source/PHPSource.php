<?php

namespace London\Config\Source;

use London\Config\Source;

class PHPSource implements Source {

	function getConfigFromPath(string $path): array {
		if (file_exists($file = "$path.php")) {
			return require $file;
		} else {
			return [];
		}
	}
}