<?php

namespace London\HTTP;

use London\Run\Request as BaseRequest;

class Request extends BaseRequest {

	static $entityParsers = [];

	public $headers;

	public $host;

	public $method;

	public $path;

	public $isSecure;

	public $uri;

	static function fromGlobals(Context $context): self {
		$session = new Session($context);
		$method = $_SERVER["REQUEST_METHOD"];
		$path = current(explode("?", $_SERVER["REQUEST_URI"], 2));
		$host = $_SERVER["HTTP_HOST"];
		$isSecure = @$_SERVER["HTTPS"] === "on";
		$headers = self::getAllHeaders();
		$data = self::getAllData($headers["accept"] ?? "text/html");

		return new self($context, $session, $method, $path, $host, $isSecure, $headers, $data);
	}

	static function getAllData(string $contentType): array {
		foreach (self::$entityParsers as $parser) {
			if (is_array($data = $parser->parse($contentType))) {
				return $data;
			}
		}

		return [];
	}

	static function getAllHeaders(): array {
		$headers = [];
		
		foreach (getallheaders() as $header=>$value) {
			$headers[strtolower($header)] = $value;
		}

		return $headers;
	}

	static function registerEntityParser(EntityParser $parser): void {
		self::$entityParsers[] = $parser;
	}

	function __construct(Context $context, Session $session, string $method, string $path, string $host, bool $isSecure, array $headers, array $data) {
		parent::__construct($context, $session, $data);
		$this->method = $method;
		$this->path = $path;
		$this->host = $host;
		$this->isSecure = $isSecure;
		$this->headers = $headers;
		$this->uri = ($isSecure ? "https" : "http") . "://{$host}/{$path}";
	}

	function getComparableState(): array {
		$state = parent::getComparableState();

		foreach (["host", "method", "path", "isSecure"] as $property) {
			$state[$property] = $this->{$property};
		}

		foreach ($this->headers as $name=>$value) {
			$state["header:$name"] = $value;
		}

		return $state;
	}
}