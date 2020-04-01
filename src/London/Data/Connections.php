<?php

namespace London\Data;

use London\Container\Container;
use London\Data\Source\DataSource;

abstract class Connections {

	static function get(string $name = "default"): DataSource {
		return Container::instance(Connections::class)->get($name);
	}

	static function set(string $name, callable $constructor) {
		Container::instance(Connections::class)->setSingleton($name, $constructor, DataSource::class);
	}
}