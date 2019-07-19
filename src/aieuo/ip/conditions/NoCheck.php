<?php

namespace aieuo\ip\conditions;

use aieuo\ip\utils\Language;

class NoCheck extends Condition {

	protected $id = self::NO_CHECK;
	protected $name = "@condition.nocheck.name";
	protected $description = "@condition.nocheck.description";

	public function getMessage() {
		return Language::get("condition.nocheck.detail");
	}

	public function check() {
		return self::MATCHED;
	}
}