<?php

namespace London\Action\MetadataSource;

use London\Action\Action;
use London\Action\MetadataSource;
use London\Annotation\Reader;
use ReflectionFunctionAbstract;
use ReflectionMethod;

class AnnotationSource implements MetadataSource {

	protected $namespaces;

	function __construct(array $namespaces) {
		$this->namespaces = $namespaces;
	}

	function getMetadata(Action $action): array {
		$meta = [];
		$function = $action->getReflection();

		if ($function instanceof ReflectionMethod) {
			$reader = new Reader($function->getDeclaringClass(), $this->namespaces);
			$source = $function->getDocComment();
			$annotations = array_merge(
				$reader->getClassAnnotations(ActionAnnotation::class),
				$reader->getMethodAnnotations($function->getName(), ActionAnnotation::class)
			);

			foreach ($annotations as $annotation) {
				$meta[$annotation->name] = $annotation->value;
			}
		}

		return $meta;
	}
}