<?php

namespace London\HTTP\EntityParser;

use London\HTTP\EntityParser;

class DefaultEntityParser implements EntityParser {

	function parse(string $contentType): ?array {
		$data = $_REQUEST;

		foreach ($data as $key=>&$value) {
			if (empty($value) && $value !== "0") {
				$value = null;
			}
		}

		foreach ($_FILES as $key=>$file) {
			$fileValue = null;

			if (is_array($file["name"])) {
				$items = [];

				foreach ($file["name"] as $i=>$fileName) {
					if (!$file["error"][$i]) {
						$items[] = new UploadedFile($fileName, $file["tmp_name"][$i], $file["type"][$i]);
					}
				}

				$fileValue = $items;
			} elseif (!$file["error"]) {
				$fileValue = new UploadedFile($file["name"], $file["tmp_name"], $file["type"]);
			}

			if ($fileValue !== null) {
				if (isset($data[$key])) {
					$data[$key] = array_merge((array) $data[$key], $fileValue);
				} else {
					$data[$key] = $fileValue;
				}
			}
		}

		return $data;
	}
}