<?php

namespace London\Annotation;

use London\Parse\Parser;
use ReflectionClass;

class Reader extends Parser {

	protected $cache = [];

	protected $class;

	protected $namespaces;

	function __construct($class, array $namespaces = [""]) {
		parent::__construct(new Grammar);
		$this->class = $class instanceof ReflectionClass ? $class : new ReflectionClass($class);
		$this->namespaces = array_merge($namespaces);
	}

	function findAnnotationClass(string $class): ?string {
		if (class_exists($class)) {
			return $class;
		}

		foreach ($this->namespaces as $ns) {
			if (class_exists($fqn = "$ns\\$class")) {
				return $fqn;
			}
		}

		return null;
	}

	function getClassAnnotation(string $annotationClass): ?Annotation {
		$annotations = $this->getClassAnnotations($annotationClass);
		return $annotations[0] ?? null;
	}

	function getClassAnnotations(string $annotationClass = null): array {
		return $this->filter($this->getAllClassAnnotations(), $annotationClass);
	}

	function getAllClassAnnotations(): array {
		if (isset($this->cache["class"])) {
			return $this->cache["class"];
		}

		$source = $this->class->getDocComment();
		return $this->cache["class"] = $this->parse($source);
	}

	function getMethodAnnotation(string $method, string $annotationClass): Annotation {
		$annotations = $this->getMethodAnnotations($method, $annotationClass);
		return @$annotations[0];
	}

	function getMethodAnnotations(string $method, string $annotationClass = null): array {
		return $this->filter($this->getAllMethodAnnotations($method), $annotationClass);
	}

	function getAllMethodAnnotations(string $method): array {
		if (isset($this->cache["method"][$method])) {
			return $this->cache["method"][$method];
		}

		$source = $this->class->getMethod($method)->getDocComment();
		return $this->cache["method"][$method] = $this->parse($source);
	}

	function getPropertyAnnotation(string $property, string $annotationClass): ?Annotation {
		$annotations = $this->getPropertyAnnotations($property, $annotationClass);
		return $annotations[0] ?? null;
	}

	function getPropertyAnnotations(string $property, string $annotationClass = null): array {
		return $this->filter($this->getAllPropertyAnnotations($property), $annotationClass);
	}

	function getAllPropertyAnnotations(string $property): array {
		if (isset($this->cache["property"][$property])) {
			return $this->cache["property"][$property];
		}

		$source = $this->class->getProperty($property)->getDocComment();
		return $this->cache["property"][$property] = $this->parse($source);
	}

	function parse(string $source): array {
		return array_filter(array_map(function($a){
			if ($class = $this->findAnnotationClass($a["class"])) {
				return new $class($a["parameters"]);
			}
		}, parent::parse($source)));

	}

	protected function filter(array $annotations, string $annotationFilter): array {
		if ($annotationFilter) {
			$annotationClass = $this->findAnnotationClass($annotationFilter);

			if (!$annotationClass) {
				throw new AnnotationException("Annotation '$annotationFilter' does not correspond to an existing class");
			}

			$annotations = array_values(array_filter($annotations, function($annotation) use($annotationClass){
				return $annotation instanceof $annotationClass;
			}));
		}
		
		return $annotations;
	}
}