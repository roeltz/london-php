<?php

namespace London\ORM\Descriptor;

interface DescriptorProvider {

	function provideForClass(string $class);
}