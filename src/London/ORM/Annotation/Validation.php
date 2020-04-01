<?php

namespace London\ORM\Annotation;

use London\Annotation\Annotation;

abstract class Validation extends Annotation {

	abstract function toArgs(): array;
}