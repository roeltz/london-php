<?php

namespace London\Templating;

interface HelperWithLifecycle {

	function startHelperLifecycle();

	function endHelperLifecycle();	
}