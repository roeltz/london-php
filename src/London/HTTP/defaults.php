<?php

namespace London\HTTP;

use London\Action\Action;
use London\Action\MetadataSource\AnnotationSource;
use London\Action\MetadataSource\ConfigSource;

Action::registerMetadataSource(new AnnotationSource([
	'London\HTTP\Annotation'
]));

Action::registerMetadataSource(new ConfigSource([
	"view-dir"=>"http.view.dir",
	"view-engine"=>"http.view.defaultEngine"
]));

Request::registerEntityParser(new EntityParser\JSONEntityParser);
Request::registerEntityParser(new EntityParser\DefaultEntityParser);