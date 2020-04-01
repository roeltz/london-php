<?php

namespace London\ORM\Annotation;

use London\Annotation\Annotation;

class Many extends Annotation {

	public $class;

	public $fk;

	public $orderBy;

	public $where;

	public $path;
}