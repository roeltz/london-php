<?php

namespace London\HTTP;

use London\Config\Config;
use London\Run\Context as BaseContext;
use London\Run\DirectRouter;
use London\Run\Request as BaseRequest;
use London\Run\Result;
use London\Run\Router as BaseRouter;
use London\Pipeline\Pipeline;

class Context extends BaseContext {

	const NAME = "http";

	public $protocol = "HTTP";

	public BaseRouter $router;
	
	function __construct(Config $config, BaseRouter $router, Request $request = null, Response $response = null) {
		parent::__construct(self::NAME, $config, $request, $response);
		$this->router = $router;
		$this->emit("created");
	}

	function createPipeline(): Pipeline {
		$pipeline = new Pipeline();
		
		$pipeline->addStep(self::STEP_INIT, function() use($pipeline){
			$this->request ?? $this->request = Request::fromGlobals($this);
			$this->response ?? $this->response = Response::fromGlobals($this);
			$pipeline->next();
		});

		$pipeline->addStep(self::STEP_ROUTING, function() use($pipeline){
			$this->request->setAction($this->router->resolve($this->request));
			$pipeline->next();
		});

		$pipeline->addStep(self::STEP_PROCESSING, function() use($pipeline){
			$this->response->setResult($this->request->action->invokeResult());
			$pipeline->next();
		});

		$pipeline->addStep(self::STEP_RENDERING, function() use($pipeline){
			$this->response->render();
			$pipeline->next();
		});

		return $pipeline;
	}

	function localURL(string $path = "/") {
		$base = $this->config->get("http.base");

		if (@$path[0] !== "/") {
			$path = "/$path";
		}

		if ($this->request) {
			$protocol = $this->request->isSecure ? "https" : "http";
			$host = $this->request->host;
		} else {
			$protocol =  @$_SERVER["HTTPS"] === "on" ? "https" : "http";
			$host = $_SERVER["HTTP_HOST"];
		}

		return "{$protocol}://{$host}{$base}{$path}";
	}

	function sub($callable, array $data = []): self {
		return new self($this->config, new DirectRouter($callable, $data));
	}
}