<?php

namespace London\HTTP;

use London\Action\Action;
use London\Action\MetadataSource\AnnotationSource;

Action::registerMetadataSource(new AnnotationSource([
	'London\Run\Annotation'
]));
