<?php

namespace London\Cache;

interface Cache {

	function destroy();

	function get(string $key);

	function has(string $key): bool;

	function remove(string $key);

	function set(string $key, $value);	
}