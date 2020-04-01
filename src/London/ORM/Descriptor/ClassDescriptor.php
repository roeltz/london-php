<?php

namespace London\ORM\Descriptor;

use London\ORM\Exception\DescriptorException;
use ReflectionClass;

class ClassDescriptor {

	const DATA_SOURCE_DEFAULT = "default";

	protected static $cache = [];

	protected static $providers = [];

	public $class;

	public $namespace;

	public $collection;

	public $dataSource = self::DATA_SOURCE_DEFAULT;

	public $embedded = false;

	public $properties = [];

	public $relations = [];

	public $sequence;

	static function forClass(string $class): self {
		if (isset(self::$cache[$class])) {
			return self::$cache[$class];
		}

		if (self::$providers) {
			foreach (self::$providers as $provider) {
				$descriptor = $provider->provideForClass($class);

				if ($descriptor) {
					self::$cache[$class] = $descriptor;
					return $descriptor;
				}
			}
			throw new DescriptorException("No ORM descriptor found for class $class");
		} else {
			throw new DescriptorException("No ORM descriptor providers are registered");
		}
	}

	static function registerProvider(DescriptorProvider $provider) {
		self::$providers[] = $provider;
	}

	function __construct(string $class) {
		$class = new ReflectionClass($class);
		$this->class = $class->getName();
		$this->namespace = $class->getNamespaceName();
	}

	function addProperty(PropertyDescriptor $descriptor) {
		$this->properties[$descriptor->name] = $descriptor;
	}

	function addRelation(RelationDescriptor $descriptor) {
		$this->relations[$descriptor->property] = $descriptor;

		if ($descriptor->isPersistent()) {
			$fk = (array) ($descriptor->fk ? $descriptor->fk : $descriptor->property);
			$this->properties[$descriptor->property]->underlyingName = $fk;
		} else {
			$this->properties[$descriptor->property]->persistent = false;
		}
	}

	function getGeneratedProperty() {
		foreach ($this->properties as $property) {
			if ($property->generated) {
				return $property;
			}
		}
	}

	function getPersistedFields(bool $underlying = true): array {
		$fields = [];

		foreach ($this->properties as $property) {
			if ($property->persistent) {
				$fields[] = $underlying ? $property->underlyingName[0] : $property->name;
			}
		}

		return $fields;
	}

	function getPrimaryKeys(bool $underlying = true): array {
		$pk = [];

		foreach ($this->properties as $property) {
			if ($property->pk) {
				$pk[] = $property->underlyingName[0];
			}
		}

		return $pk;
	}

	function getPropertyDescriptor(string $property): PropertyDescriptor {
		if ($descriptor = @$this->properties[$property]) {
			return $descriptor;
		} else {
			throw new DescriptorException("Invalid property {$this->class}::{$property}, when looking for a property descriptor");
		}
	}

	function getRelatedClassDescriptor(string $property): self {
		return ClassDescriptor::forClass($this->getRelationDescriptor($property)->class);
	}

	function getRelationDescriptor(string $property): RelationDescriptor {
		if ($descriptor = @$this->relations[$property]) {
			return $descriptor;
		} else {
			throw new DescriptorException("Invalid property {$this->class}::{$property}, when looking for a relation descriptor");
		}
	}

	function hasRelation(string $property) {
		return isset($this->relations[$property]) ? $this->relations[$property] : false;
	}

	function normalizeClass(string $class): string {
		if (!$this->namespace || strpos($class, '\\') === 0) {
			return $class;
		} else {
			return $this->namespace.$class;
		}
	}
}