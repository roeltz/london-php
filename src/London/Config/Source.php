<?php

namespace London\Config;

interface Source {

	function getConfigFromPath(string $path): array;
}