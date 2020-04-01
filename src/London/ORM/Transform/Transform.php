<?php

namespace London\ORM\Transform;

use London\ORM\Entity;

interface Transform {

	function apply(TransformInfo $info);

	function revert(TransformInfo $info);
}