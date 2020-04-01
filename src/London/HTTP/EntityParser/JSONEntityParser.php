<?php

namespace London\HTTP\EntityParser;

use London\HTTP\EntityParser;

class JSONEntityParser implements EntityParser {

	function parse(string $contentType): ?array {
		if (strpos($contentType, "application/json") !== false) {
			$data = @json_decode(file_get_contents("php://input"), true);
			return array_merge($_REQUEST, (array) $data);
		}
		
		return null;
	}
}