<?php

namespace London\Event;

use Closure;

class EventEmitter {

	const FLAG_ONCE = 1;

	protected static $expected = [];
	
	protected $bubbles = [];
	
	protected $listeners = [];

	static function expect(string $event, callable $callable) {
		self::$expected[get_called_class()][$event][] = (object) [
			"callable"=>$callable,
			"flags"=>0
		];
	}

	static function expectOnce(string $event, callable $callable) {
		self::$expected[get_called_class()][$event][] = (object) [
			"callable"=>$callable,
			"flags"=>self::FLAG_ONCE
		];
	}

	function __construct() {
		foreach (self::$expected as $class=>$entries) {
			if ($this instanceof $class) {
				$this->listeners = array_merge_recursive($this->listeners, $entries);
			}
		}
	}

	function bubble(EventEmitter $emitter, string $prefix = null, array $filterEvents = null) {
		$this->bubbles[] = new EventBubble($emitter, $prefix, $filterEvents);
	}

	function emit(string $event, ...$arguments) {
		if (isset($this->listeners[$event])) {
			$entries = $this->listeners[$event];

			foreach ($entries as $i=>$entry) {
				$closure = Closure::fromCallable($entry->callable);
				$closure->call($this, ...$arguments);

				if ($entry->flags & self::FLAG_ONCE) {
					unset($this->listeners[$event][$i]);
				}
			}
		}

		if ($this->bubbles) {
			foreach ($this->bubbles as $bubble) {
				$bubble->emit($event, ...$arguments);
			}
		}
	}

	function on(string $event, callable $callable) {
		$this->addListener($event, $callable);
	}

	function once(string $event, callable $callable) {
		$this->addListener($event, $callable, self::FLAG_ONCE);
	}

	function off(string $event, callable $callable = null): bool {
		if (isset($this->listeners[$event])) {
			if ($callable) {
				foreach ($this->listeners[$event] as $i=>$c) {
					if ($c->callable === $callable) {
						unset($this->listeners[$event][$i]);
						return true;
					}
				}
			} else {
				unset($this->listeners[$event]);
			}
		}
	}

	protected function addListener(string $event, callable $callable, int $flags = 0) {
		$this->listeners[$event][] = (object) [
			"callable"=>$callable,
			"flags"=>$flags
		];
	}
}