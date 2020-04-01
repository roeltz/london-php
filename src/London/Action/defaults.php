<?php

namespace London\Action;

use DateTime;
use ReflectionParameter;

Action::registerCast(ActionArgument::class, function($value, ReflectionParameter $parameter){
	$instance = $parameter->getClass()->newInstance();
	$instance->fromArgumentValue($value);
	return $instance;
});

Action::registerCast(DateTime::class, function($value){
	if (is_numeric($value)) {
		$date = new DateTime();
		$date->setTimestamp($value);
		return $date;
	} elseif (is_string($value) && strlen($value)) {
		return new DateTime($value);
	} else {
		return null;
	}
});
