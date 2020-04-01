<?php

namespace London\ORM\Transform;

class HashTransform implements Transform {
		
	function apply(TransformInfo $info) {
		if (preg_match('/^#/', $info->value)) {
			return $info->value;
		} else {
			$salt = @$info->args["salt"];
			$hash = hash($info->args["algo"], "{$salt}{$value}");
			return "#$hash";
		}
	}
	
	function revert(TransformInfo $info) {
		return $info->value;
	}
}
