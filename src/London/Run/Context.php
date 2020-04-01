<?php

namespace London\Run;

use London\Config\Config;
use London\Event\EventEmitter;
use London\Pipeline\Pipeline;

abstract class Context extends EventEmitter {
	use Receiver;

	const STEP_INIT = "init";
	const STEP_ROUTING = "routing";
	const STEP_PROCESSING = "processing";
	const STEP_RENDERING = "rendering";

	public Config $config;

	public string $name;

	public Pipeline $pipeline;

	public ?Request $request;
	
	public ?Response $response;

	protected static array $hookPaths = [];

	abstract function createPipeline(): Pipeline;
	
	static function create(...$arguments) {
		return new static(...$arguments);
	}

	static function hook(...$hooks) {
		foreach ($hooks as $hook) {
			foreach (self::$hookPaths as $path) {
				if (file_exists($file = "$path/$hook.php")) {
					require_once $file;
					break;
				}
			}
		}
	}

	static function registerHookPath($path) {
		self::$hookPaths[] = $path;
	}

	function __construct($name, Config $config, Request $request = null, Response $response = null) {
		parent::__construct();
		$this->name = $name;
		$this->config = $config;
		$this->request = $request;
		$this->response = $response;
		$this->pipeline = $this->createPipeline();
		$this->pipeline->bubble($this, "pipeline:");
		$this->emit("created");
	}

	function run() {
		return $this->pipeline->run();
	}

	function toActionContext(): array {
		return [
			$this,
			$this->request,
			$this->request->action,
			$this->request->session,
			$this->request->session->getUser(),
			$this->response,
			$this->response->view
		];
	}
}