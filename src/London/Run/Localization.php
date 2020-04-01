<?php

namespace London\Run;

use London\Locale\Locale;
use London\Locale\Resource;

class Localization {

	const STEP_LOCALIZATION = "localization";
	
	protected Context $context;
	
	protected array $extractors = [];
	
	static function attach(Context $context) {
		return new self($context);
	}

	function __construct(Context $context) {
		$this->context = $context;
		$this->bind();
	}
	
	function accept(...$args): self {
		Locale::accepted($args);
		return $this;
	}

	function addExtractor(LocaleExtractor $extractor): self {
		$this->extractors[] = $extractor;
		return $this;
	}
	
	function addResource(string $filename, string $domain = "default"): self {
		Locale::registerResourceFilename($filename, $domain);
		return $this;
	}
	
	function bind() {
		$this->context->localization = $this;
		$this->context->pipeline->addStepAfter(self::STEP_LOCALIZATION, Context::STEP_INIT, function(){
			$this->context->request->receive("locale", $this->resolve());
			$this->context->pipeline->next();
		});
	}
	
	function resolve(): Locale {
		foreach ($this->extractors as $extractor) {
			if ($locale = $extractor->getLocale($this->context->request)) {
				$locale->setEnvironment();
				return $locale;
			}
		}
	}
}
