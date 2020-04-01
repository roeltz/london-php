<?php

namespace London\Action\MetadataSource;

use London\Action\Action;
use London\Action\MetadataSource;
use London\Config\Config;
use ReflectionFunctionAbstract;

class ConfigSource implements MetadataSource {

	protected $map;

	function __construct(array $map = []) {
		$this->map = $map;
	}

	function getMetadata(Action $action): array {
		$meta = [];

		foreach ($this->map as $option=>$key) {
			$meta[$option] = $action->context->config->get($key);
		}

		return $meta;
	}
}
