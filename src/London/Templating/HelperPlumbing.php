<?php

namespace London\Templating;

abstract class HelperPlumbing {

	/**
	 * @var Helper[]
	 */
	static protected $globalHelpers = [];

	/**
	 * @var Helper[]
	 */
	protected $helpers = [];

	protected $helpersInited = false;

	static function addGlobalHelper($name, $helper) {
		self::$globalHelpers[$name] = self::cast($helper);
	}

	static function cast($helper) {
		return is_string($helper) ? new $helper() : $helper;
	}

	function addHelper(string $name, $helper) {
		$this->helpers[$name] = self::cast($helper);
		return $this;
	}

	function endHelpersLifecycles() {
		foreach ($this->helpers as $helper) {
			if ($helper instanceof HelperWithLifecycle) {
				$helper->endHelperLifecycle();
			}
		}
	}

	function getHelper(string $name) {
		return @$this->helpers[$name];
	}

	function initAllHelpers(&$data, array &$options, Engine $callingEngine, $callingFile = null) {
		if ($this->helpersInited) return $this->helpersInited;
		
		return $this->helpersInited = array_merge(
			$this->initHelpers(self::$globalHelpers, $data, $options, $callingEngine, $callingFile),
			$this->initHelpers($this->helpers, $data, $options, $callingEngine, $callingFile)
		);
	}

	function initHelpers(array $helpers, &$data, array &$options, Engine $callingEngine, $callingFile = null) {
		$objects = [];
		foreach ($helpers as $name=>$helper) {
			if ($helper instanceof Helper) {
				$helper->initHelper($data, $options, $callingEngine, $callingFile);
			}

			$objects[$name] = $helper;
		}
		return $objects;
	}

	function startHelpersLifecycles() {
		foreach ($this->helpers as $helper) {
			if ($helper instanceof HelperWithLifecycle) {
				$helper->startHelperLifecycle();
			}
		}
	}
}