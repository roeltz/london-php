<?php

namespace London\HTTP;

interface EntityParser {

	function parse(string $contentType): ?array;
}