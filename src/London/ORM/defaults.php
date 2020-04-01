<?php

namespace London\ORM;

use London\ORM\Descriptor\AnnotationDescriptorProvider;
use London\ORM\Descriptor\ClassDescriptor;
use London\ORM\Transform\HashTransform;
use London\ORM\Transform\JSONTransform;
use London\ORM\Validation\NotNullValidation;
use London\ORM\Validation\PatternValidation;

ClassDescriptor::registerProvider(new AnnotationDescriptorProvider());

ORM::registerTransform("json", JSONTransform::class);
ORM::registerTransform("hash", HashTransform::class);

ORM::registerValidation("notnull", NotNullValidation::class);
ORM::registerValidation("pattern", PatternValidation::class);