<?php

namespace London\Locale;

Locale::registerResourceClass('London\Locale\MoResource', function($path){
	return preg_match('/\.mo$/i', $path);
});
