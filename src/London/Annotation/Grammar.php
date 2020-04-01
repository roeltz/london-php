<?php

namespace London\Annotation;

use London\Parse\Grammar as BaseGrammar;
use London\Parse\Stack;
use London\Parse\SyntaxError;

class Grammar extends BaseGrammar {

	function __construct() {

		parent::__construct(BaseGrammar::RECOVERABLE);

		$this
			->add("annotation")
			->add("non-annotation")
			->end(function($nodes){
				return array_filter($nodes, function($n){
					return is_array($n);
				});
			})
		;

		$this->regex("non-annotation", '[^@]+');

		$this->nonterminal("annotation")
			->terminal("sigil", "@")
			->rule("class")
			->optionalRule("annotation-parameters", [])
			->end(function($m, $stack, $input, $n, $l){
				return [
					"class"=>$m["class"],
					"parameters"=>(array) @$m["annotation-parameters"]
				];
			})
		;

		$this->nonterminal("class")
			->regex("fqn", '(\w+\\\\)*\w+', 'i')
			->end(function($m){
				return $m["fqn"];
			})
		;

		$this->nonterminal("annotation-parameters")
			->regex("annotation-parameters-opening", '\(\s*')
			->optionalRule("annotation-parameters-content")
			->regex("annotation-parameters-closing", '\s*\)')
			->end(function($m){
				return @$m["annotation-parameters-content"];
			})
		;

		$this->alternative("annotation-parameters-content")
			->rule("value")
			->rule("array-content")
			->end(function($m, $rule){
				return $rule === "value" ? ["value"=>$m] : $m;
			})
		;

		$this->nonterminal("array")
			->regex("array-opening", '\s*\[\s*')
			->optionalRule("array-content")
			->regex("array-closing", "\s*\]\s*")
			->end(function($m){
				return @$m["array-content"];
			})
		;

		$this->nonterminal("array-content")
			->rule("first", "array-item")
			->multiple("rest")
				->rule("array-rest-item")
				->end()
			->end(function($m){
				$arguments = $m["first"];
				if (isset($m["rest"])) {
					foreach ($m["rest"] as $item) {
						$arguments = array_merge($arguments, $item["array-rest-item"]);
					}
				}
				return $arguments;
			})
		;

		$this->alternative("array-item")
			->rule("value")
			->rule("assignment")
			->end(function($m, $rule){
				return $rule === "assignment" ? $m : [$m];
			})
		;

		$this->nonterminal("array-rest-item")
			->regex("separator", '\s*,\s*')
			->rule("array-item")
			->end(function($m){
				return $m["array-item"];
			})
		;

		$this->alternative("value")
			->rule("string")
			->rule("number")
			->rule("boolean")
			->rule("null")
			->rule("constant")
			->rule("array")
			->rule("annotation")
		;

		$this->nonterminal("assignment")
			->regex("property", '\w+')
			->regex("equals", '\s*=\s*')
			->rule("value")
			->end(function($m){
				return array($m["property"]=>$m["value"]);
			})
		;

		$this->nonterminal("string")
			->terminal("string-opening", '"')
			->regex("content", '([^"\\\\]|\\\\.)*')
			->terminal("string-closing", '"')
			->end(function($m){
				return json_decode('"' . $m["content"] . '"');
			})
		;

		$this->nonterminal("number")
			->regex("content", '-?\d+(\.\d+)?')
			->end(function($m){
				return (float) $m["content"];
			})
		;

		$this->alternative("boolean")
			->terminal("true", "true")
			->terminal("false", "false")
			->end(function($m, $rule){
				return $rule === "true" ? 1 : 0;
			})
		;

		$this->nonterminal("null")
			->terminal("null", "null")
			->end(function(){
				return null;
			})
		;

		$this->alternative("constant")
			->regex("name", '[A-Z_]+')
			->nonterminal("fqn")
				->rule("class")
				->terminal("separator", "::")
				->rule("name")
				->end(function($m){
					return "{$m['class']}::{$m['name']}";
				})
			->end(function($m, $rule, Stack $stack){
				if ($rule === "name") {
					$fqn = $stack->get("class")."::$m";

					if (defined($fqn)) {
						$m = $fqn;
					}
				}

				if (defined($m)) {
					return constant($m);
				} else {
					$fqn = $stack->get("class");
					throw new AnnotationException("Constant '$m' not defined for annotation class '$fqn'");
				}
			})
		;
	}
}