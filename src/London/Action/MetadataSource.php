<?php

namespace London\Action;

use ReflectionFunctionAbstract;

interface MetadataSource {

	function getMetadata(Action $action): array;
}