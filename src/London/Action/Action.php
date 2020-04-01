<?php

namespace London\Action;

use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

class Action {

	const METHOD_PATTERN = '/^((?:\w+(?:\\\\\w+)*)?\w+)::?(\w+)$/';

	protected static $casts = [];

	protected static $metadataSources = [];
	
	public $callable;

	static function from($callable) {
		return new Action($callable);
	}

	static function registerCast(string $class, callable $castFunction): void {
		self::$casts[$class] = $castFunction;
	}

	static function registerMetadataSource(MetadataSource $source): void {
		self::$metadataSources[] = $source;
	}

	function __construct($callable) {
		$this->callable = $callable;
	}

	function castArgument(ReflectionParameter $parameter, $value) {
		if ($class = $parameter->getClass()) {
			foreach (self::$casts as $className=>$castFunction) {
				if ($class->isSubclassOf($className)) {
					return $castFunction($value, $parameter);
				}
			}
		} else {
			return $value;
		}
	}

	function computeArgumentList(array $arguments, array $context): array {
		$reflection = $this->getReflection();
		$list = [];

		foreach ($reflection->getParameters() as $parameter) {
			$name = $parameter->getName();

			if (!is_null($value = @$arguments[$name])) {
				$list[] = $this->castArgument($parameter, $value);
			} elseif ($class = $parameter->getClass()) {
				foreach ($context as $value) {
					if (is_object($value) && $class->isInstance($value)) {
						$list[] = $value;
						break;
					}
				}
			} elseif ($parameter->isOptional()) {
				$list[] = $parameter->getDefaultValue();
			} else {
				$list[] = null;
			}
		}

		return $list;
	}

	function getInstanceForMethod(ReflectionMethod $method): ?object {
		if ($method->isStatic()) {
			return null;
		} elseif (is_array($this->callable) && is_object($this->callable[0])) {
			return $this->callable[0];
		} else {
			return $method->getDeclaringClass()->newInstance();
		}
	}

	function getMetadata(string $key = null) {
		$meta = [];

		foreach (self::$metadataSources as $source) {
			$meta = array_merge($meta, $source->getMetadata($this));
		}

		if ($key) {
			return @$meta[$key];
		} else {
			return $meta;
		}
	}

	function getReflection(): ReflectionFunctionAbstract {
		is_callable($this->callable, false, $name);

		if ($name === "Closure::__invoke") {
			return new ReflectionFunction($this->callable);
		} elseif (preg_match(self::METHOD_PATTERN, $name, $match)) {
			return new ReflectionMethod($match[1], $match[2]);
		} else {
			return new ReflectionFunction($name);
		}
	}

	function invoke(array $arguments, array $context = []) {
		$reflection = $this->getReflection();
		$arguments = $this->computeArgumentList($arguments, $context);

		if ($reflection instanceof ReflectionMethod) {
			return $reflection->invokeArgs($this->getInstanceForMethod($reflection), $arguments);
		} else {
			return $reflection->invokeArgs($arguments);
		}
	}
}