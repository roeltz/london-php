<?php

namespace London\HTTP\View;

use London\HTTP\Response;
use London\Run\Result;
use London\Templating\JSONEngine as BaseJSONEngine;

class JSONEngine extends BaseJSONEngine implements Engine {

	function renderResponse(Response $response): string {
		$response->setHeader("Content-Type", "application/json");
		return $this->render($response->result->data, $response->result->options);
	}
}