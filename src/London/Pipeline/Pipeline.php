<?php

namespace London\Pipeline;

use Exception;
use London\Event\EventEmitter;
use London\Util\Arrays;

class Pipeline extends EventEmitter {

	public $lastException;
	
	protected $atEnd = false;

	protected $currentStep = null;
	
	protected $result;
	
	protected $runNext = false;

	protected $steps = [];

	function addStep(string $key, callable $callable): self {
		$this->steps[$key] = $callable;
		return $this;
	}

	function addStepAfter($key, $otherKey, callable $callable): self {
		[$before, $after, $value] = Arrays::splitByKey($this->steps, $otherKey);
		$this->steps = array_merge($before, [$otherKey=>$value], [$key=>$callable], $after);
		return $this;
	}

	function addStepBefore($key, $otherKey, callable $callable): self {
		[$before, $after, $value] = Arrays::splitByKey($this->steps, $otherKey);
		$this->steps = array_merge($before, [$key=>$callable], [$otherKey=>$value], $after);
		return $this;
	}

	function next() {
		$this->runNext = true;
	}

	function prependStep($key, callable $callable): self {
		$this->steps = array_merge([$key=>$callable], $this->steps);
		return $this;
	}

	function replaceStep($key, $otherKey, callable $callable): self {
		[$before, $after] = Arrays::splitByKey($this->steps, $otherKey);
		$this->steps = array_merge($before, [$key=>$callable], $after);
		return $this;
	}

	function run() {
		if (!$this->atEnd) {
			$this->runNextStep();
			return $this->result;
		}
	}
	
	function runNextStep() {
		$keys = array_keys($this->steps);
		$index = $this->currentStep === null ? 0 : array_search($this->currentStep, $keys) + 1;

		if ($index < count($keys)) {
			$this->currentStep = $keys[$index];
			$this->runStep($this->currentStep);
			return true;
		} else {
			$this->atEnd = true;
			return false;
		}
	}

	function runStep(string $step) {
		$this->runNext = false;

		if ($callable = @$this->steps[$step]) {
			$this->currentStep = $step;

			try {
				$this->emit("before", $step);
				$this->emit("before:$step");
				
				$result = $callable($this);

				if ($result !== null) {
					$this->result = $result;
				}

				$this->emit("after", $step);
				$this->emit("after:$step");
			} catch (Exception $ex) {
				$this->emit("error", $ex);
			}

			if ($this->runNext) {
				$this->runNextStep();
			}
		} else {
			throw new PipelineException("Step '$step' does not exist in this pipeline");
		}
	}
}