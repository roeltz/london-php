<?php

namespace London\Util;

abstract class Arrays {

	static function flatten(array $array, bool $filterNulls = true): array {
		$flat = (object) ["a"=>[]];
		
		array_walk_recursive($array, function(&$v, &$k) use($flat, $filterNulls){
			if (!$filterNulls || !is_null($v)) {
				$flat->a[] = $v;
			}
		});

		return $flat->a;
	}

	static function index(array $array, string $indexKey) {
		$indexed = [];

		foreach ($array as $item) {
			$indexed[$item[$indexKey]] = $item;
		}

		return $indexed;
	}

	static function isAssoc(array $array): bool {
		if (is_array($array)) {
			return (bool) count(array_filter(array_keys($array), "is_string"));
		} else {
			return false;
		}
	}

	static function remove(array $array, $value): array {
		foreach ($array as $i=>$v) {
			if ($v === $value) {
				unset($array[$i]);
			}
		}

		return $array;
	}

	static function splitByKey(array $array, string $key, bool $strict = false) {
		$index = array_search($key, array_keys($array), $strict);

		if ($index !== false) {
			$before = array_slice($array, 0, $index, true);
			$after = array_slice($array, $index + 1, null, true);
			$value = $array[$key];

			return [$before, $after, $value];
		} else {
			return false;
		}
	}
}