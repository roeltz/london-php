<?php

namespace London\Parse;

interface Rule {
	
	function matches(string $input, int $n, int &$l, Stack $stack);
}
