<?php

namespace London\Run;

use Exception;

class Result {

	public $data;

	public array $options;

	static function from($data, array $options = []): self {
		if ($data instanceof self) {
			if ($options) {
				$data->mergeOptions($options);
			}

			return $data;
		} else {
			return new Result($data, $options);
		}
	}

	function __construct($data, array $options) {
		$this->data = $data;
		$this->options = $options;
	}

	function mergeData(array $data) {
		if (is_array($data)) {
			$this->data = array_merge($this->data, $data);
		} else {
			throw new Exception("Cannot merge data into result: data is not an array");
		}
	}

	function mergeOptions(array $options) {
		$this->options = array_merge($this->options, $options);
	}
}