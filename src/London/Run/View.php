<?php

namespace London\Run;

interface View {

	function render(Response $response);
}