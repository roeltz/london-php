<?php

namespace London\HTTP\View;

use London\HTTP\Response;
use London\Run\Result;

interface Engine {

	function renderResponse(Response $response): string;
}
