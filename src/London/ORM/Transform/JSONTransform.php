<?php

namespace London\ORM\Transform;

class JSONTransform implements Transform {
		
	function apply(TransformInfo $info) {
		$flags = JSON_UNESCAPED_SLASHES;

		if ($info->args["pretty"] ?? false) {
			$flags |= JSON_PRETTY_PRINT;
		}

		return json_encode($info->value, $flags);
	}
	
	function revert(TransformInfo $info) {
		$assoc = ($info->args["returnType"] ?? "object") === "array";
		return json_decode($info->value, $assoc);
	}
}