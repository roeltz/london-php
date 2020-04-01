<?php

namespace London\ORM\Annotation;

use London\Annotation\Annotation;

abstract class Transform extends Annotation {

	abstract function toArgs(): array;
}