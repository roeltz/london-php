<?php

namespace London\Event;

class EventBubble {

	protected $emitter;

	protected $prefix;

	protected $filterEvents;

	function __construct(EventEmitter $emitter, string $prefix = null, array $filterEvents = null) {
		$this->emitter = $emitter;
		$this->prefix = $prefix;
		$this->filterEvents = $filterEvents;
	}

	function emit(string $event, ...$arguments) {
		if (!$this->filterEvents || in_array($event, $this->filterEvents)) {
			if ($this->prefix) {
				$event = "{$this->prefix}{$event}";
			}
			
			$this->emitter->emit($event, ...$arguments);
		}
	}
}