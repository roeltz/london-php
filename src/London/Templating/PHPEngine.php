<?php

namespace London\Templating;

use Exception;

class PHPEngine extends HelperPlumbing implements Engine {

	function __construct() {
		$this->addHelper("html", Helper\HTML::class);
		$this->addHelper("layout", Helper\Layout::class);
		$this->addHelper("view", Helper\PHP::class);
	}

	function render($__data, array $__options) {
		$__file = isset($__options['view-layout'])
			? "{$__options['view-dir']}/{$__options['view-layout']}.php"
			: "{$__options['view-dir']}/{$__options['view']}.php"
		;

		extract($this->initAllHelpers($__data, $__options, $this, $__file));

		if (is_array($__data)) {
			extract($__data);
		}
		
		ob_start();

		$this->startHelpersLifecycles();

		try {
			require $__file;
		} catch (Exception $ex) {
			ob_end_clean();
			throw $ex;
		}

		$this->endHelpersLifecycles();
		return ob_get_clean();
	}

	function setAllHelpers() {
		
	}
}