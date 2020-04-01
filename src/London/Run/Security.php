<?php

namespace London\Run;

use London\Pipeline\Pipeline;
use London\Run\Annotation\Secured;
use London\Run\Exception\SecurityException;

class Security {

	const STEP_SECURITY = "security";

	protected $context;

	protected $roles = [];
	
	protected $reverse = [];

	static function attach(Context $context) {
		return new self($context);
	}

	function __construct(Context $context) {
		$this->context = $context;
		$this->bind();
	}

	function addRole($role, ...$dependencies) {
		$this->roles[$role] = array_slice(func_get_args(), 1);
		$this->reverse[$role] = array();
		$this->computeReverseRoles();
		return $this;
	}
	
	function bind() {
		$this->context->security = $this;
		$this->context->pipeline->addStepBefore(self::STEP_SECURITY, Context::STEP_PROCESSING, function(){
			if ($this->isActionAllowed()) {
				$this->context->pipeline->next();
			} else {
				throw new SecurityException("Current user cannot run this action");
			}
		});
	}

	function getGreaterRoles($role) {
		$greater = $this->reverse[$role];
		
		foreach ($this->reverse[$role] as $r) {
			$greater = array_unique(array_merge($greater, $this->getGreaterRoles($r)));
		}

		return $greater;
	}
	
	function getLesserRoles($role) {
		$lesser = $this->roles[$role];
		
		foreach ($this->roles[$role] as $r) {
			$lesser = array_unique(array_merge($lesser, $this->getLesserRoles($r)));
		}
		
		return $lesser;
	}

	function isActionAllowed(Action $action = null, User $user = null) {
		$action ?? $action = $this->context->request->action;
		$user ?? $user = $this->context->request->session->getUser();

		if (!$user) {
			$action->context->emit("user-needed");
		}

		$constraints = $action->getMetadata(Secured::NAME);
		return $this->isAllowed($user, $constraints);
	}

	function isAllowed($user, $constraints) {
		if (!$constraints) {
			return true;
		}

		$roles = $user instanceof User ? $user->getUserRoles() : [];

		if ($constraints) {
			if ($constraints === "*" && $user) {
				return true;
			} else {
				$constraints = (array) $constraints;
				$configAllowed = [];
				$configDenied = [];

				foreach ($constraints as $requestedRole) {
					if (preg_match('#^!#', $requestedRole)) {
						$configDenied[] = $requestedRole;
					} else {
						$configAllowed[] = $requestedRole;
					}
				}

				$allow = (in_array("*", $configAllowed) && $user) || $this->isAnyRoleIncluded($roles, $configAllowed);
				$deny = in_array("*", $configDenied) || $this->isAnyRoleIncluded($roles, $configDenied);
				$denyFirst = in_array("#denyfirst", $constraints);

				return $denyFirst ? (!$deny || $allow) : ($allow && !$deny);
			}
		}
	}

	protected function computeReverseRoles() {
		foreach (array_keys($this->roles) as $role) {
			$stack = array($role);

			while ($stack) {
				$item = array_shift($stack);

				foreach ($this->roles as $r=>$rs) {
					if (in_array($item, $rs)) {
						if (!in_array($r, $this->reverse[$role])) {
							$this->reverse[$role][] = $r;
							$stack[] = $r;
						}
					}
				}
			}
		}

		foreach ($this->reverse as $r=>$rs) {
			foreach ($rs as $rr) {
				$this->reverse[$r] = array_unique(array_merge($this->reverse[$r], $this->reverse[$rr]));
			}
		}
	}

	protected function isAnyRoleIncluded(array $concrete, array $annotated) {
		foreach ($concrete as $c) {
			$pattern = "/^$c\$/";

			foreach ($annotated as $a) {
				if (preg_match($pattern, $a)) {
					return true;
				}
			}
		}
		
		foreach ($annotated as $a) {
			foreach ((array) @$this->reverse[$a] as $r) {
				$pattern = "/^$r\$/";
				
				foreach ($concrete as $c) {
					if (preg_match($pattern, $c)) {
						return true;
					}
				}
			}
		}
	}
}