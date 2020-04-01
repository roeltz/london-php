<?php

namespace London\Templating;

interface ViewFilter {

	function process(string $buffer): string;
}