<?php

namespace London\Templating;

interface Engine {

	function render($data, array $options);
}