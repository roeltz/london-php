<?php

namespace London\Util;

abstract class Objects {

	static function traverse($object, $path, $write, $newValue = null) {
		$steps = is_array($path) ? $path : explode(".", $path);

		if ($steps) {
			$value = null;

			if (is_object($object)) {
				$value = &$object->{$steps[0]};
			} elseif (is_array($object)) {
				$value = &$object[$steps[0]];
			}

			if ($write && count($steps) == 1) {
				$value = $newValue;
			} elseif (!$write && $value !== null) {
				return self::traverse($value, array_slice($steps, 1), $write, $newValue);
			}

			return $value;
		}
	}

	static function walk(&$object, callable $callback, $key = null, &$source = null, array &$visited = []) {
		if (is_object($object) && in_array($object, $visited, true)) {
			return;
		} elseif (is_object($object)) {
			$visited[] = $object;
		}

		$result = $callback($object, $key);

		if ($result !== false && (is_object($object) || is_array($object))) {
			foreach ($object as $key=>&$value) {
				self::walk($value, $callback, $key, $object, $visited);
			}
		}
	}
}