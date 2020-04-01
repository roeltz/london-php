<?php

namespace London\ORM;

use London\Container\Container;
use London\Data\Connections;
use London\ORM\Descriptor\ClassDescriptor;
use London\ORM\Descriptor\MultipleRelationDescriptor;
use London\ORM\Exception\ValidationException;
use London\ORM\Mapper\RelationalMapper;
use London\ORM\Query\ORMQuery;
use London\ORM\Transform\Transform;
use London\ORM\Transform\TransformInfo;
use London\ORM\Validation\Validation;
use London\ORM\Validation\ValidationInfo;

abstract class ORM {

	protected static $namespacedDataSources = [];

	protected static $instanceCache;

	protected static $transforms = [];

	protected static $validations = [];

	static function buildPK(ClassDescriptor $descriptor, array $args) {
		$pk = [];

		foreach($descriptor->getPrimaryKeys() as $i=>$property) {
			$pk[$property] = $args[$i];
		}

		return $pk;
	}

	static function cast(Entity $instance, array $record, array $hints = null) {
		if (!$hints) {
			$hints = self::getCastingHints(ClassDescriptor::forClass(get_class($instance)));
		}

		[$set, $transforms, $bring, $unset, $computed] = $hints;

		foreach ($set as $propertyName=>$underlyingName) {
			if (count($underlyingName) === 1) {
				$value = $record[$underlyingName[0]];
			} else {
				$value = [];

				foreach ($underlyingName as $fieldName) {
					$value[] = $record[$fieldName];
				}
			}
			$instance->$propertyName = $value;
		}

		foreach ($transforms as $propertyName=>$propertyTransforms) {
			$value = $record[$propertyName];

			foreach ($propertyTransforms as $transformName=>$transformArgs) {
				$transformInstance = self::getTransformInstance($transformName);
				$value = $transformInstance->revert(new TransformInfo($transformArgs, $instance, $propertyName, $value));
			}

			$instance->$propertyName = $value;
		}

		foreach ($bring as $propertyName) {
			$instance->bring($propertyName);
		}

		foreach ($unset as $propertyName) {
			unset($instance->$propertyName);
		}

		foreach ($computed as $propertyName=>$method) {
			$instance->$propertyName = call_user_func([$instance, $method]);
		}
	}

	static function getCastingHints(ClassDescriptor $classDescriptor) {
		$set = [];
		$computed = [];
		$transforms = [];
		$bring = [];
		$unset = [];

		foreach ($classDescriptor->properties as $propertyName=>$propertyDescriptor) {
			if ($classDescriptor->hasRelation($propertyName)) {
				if ($propertyDescriptor->eager) {
					$bring[] = $propertyName;
				} else {
					$unset[] = $propertyName;
				}
			} elseif ($method = $propertyDescriptor->computedBy) {
				$computed[$propertyName] = $method;
			} elseif ($transformations = $propertyDescriptor->transformations) {
				$transforms[$propertyName] = $transformations;
			} else {
				$set[$propertyName] = $propertyDescriptor->underlyingName;
			}
		}

		return [$set, $transforms, $bring, $unset, $computed];
	}

	static function getDataSource(string $class) {
		if (is_object($class)) {
			$class = get_class($class);
		}

		foreach (self::$namespacedDataSources as $ns=>$ds) {
			if (strpos(static::class, "$ns\\") === 0) {
				return Connections::get($ds);
			}
		}

		$dataSource = ClassDescriptor::forClass($class)->dataSource;
		return Connections::get($dataSource);
	}

	static function getDescriptor($class): ClassDescriptor {
		if (is_object($class)) {
			$class = get_class($class);
		}

		return ClassDescriptor::forClass($class);
	}

	static function getInstanceCache(string $class) {
		if (!self::$instanceCache) {
			self::$instanceCache = new InstanceCache();
		}

		return self::$instanceCache;
	}

	static function getMapper($class) {
		return new RelationalMapper();
	}

	static function getPersistedValues(Entity $instance, bool $isUpdate = false, array $properties = []) {
		$values = [];
		$descriptor = self::getDescriptor($instance);

		if (!$properties) {
			$properties = $descriptor->getPersistedFields();
		}

		foreach ($properties as $property) {
			$property = $descriptor->getPropertyDescriptor($property);
			$relation = $descriptor->hasRelation($property->name);
			$isMany = $relation instanceof MultipleRelationDescriptor;

			if (!$isMany && (!$isUpdate || !$property->generated)) {
				$value = $instance->{$property->name};

				self::validateProperty($instance, $property->name, $value);

			}
		}

		return $values;
	}

	static function getPrimaryKeyValues(Entity $instance) {
		$descriptor = self::getDescriptor($instance);
		$pk = $descriptor->getPrimaryKeys();

		return self::getPersistedValues($instance, true, $pk);
	}

	static function getTransformInstance(string $name): Transform {
		return Container::instance(self::class)->get("transform:$name");
	}

	static function getValidationInstance(string $name): Validation {
		return Container::instance(self::class)->get("validation:$name");
	}

	static function newQuery(string $class) {
		if (is_object($class)) {
			$class = get_class($class);
		}

		$dataSource = self::getDataSource($class);
		$descriptor = self::getDescriptor($class);

		return new ORMQuery($dataSource, $descriptor);
	}

	static function registerTransform(string $name, string $class) {
		Container::instance(self::class)->setSingleton("transform:$name", function() use($class){
			return new $class;
		});
	}

	static function registerValidation(string $name, string $class) {
		Container::instance(self::class)->setSingleton("validation:$name", function() use($class){
			return new $class;
		});
	}

	static function validateProperty(Entity $instance, string $property) {
		$descriptor = self::getDescriptor($instance);
		$property = $descriptor->getPropertyDescriptor($property);
		$value = $instance->{$property->name};

		foreach ($property->validations as $name=>$args) {
			$validationInstance = self::getValidationInstance($name);
			$validationInstance->validate(new ValidationInfo($args, $instance, $property->name, $value));
		}
	}
}
