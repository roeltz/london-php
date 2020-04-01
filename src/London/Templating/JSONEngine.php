<?php

namespace London\Templating;

class JSONEngine implements Engine {

	function render($data, array $options) {
		return json_encode($data);
	}
}