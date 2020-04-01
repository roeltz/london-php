<?php

namespace London\Run;

interface Router {

	function resolve(Request $request): Action;
}