<?php

namespace aieuo\ip\conditions;

class NoCheck extends Condition {

	protected $id = self::NO_CHECK;
	protected $name = "何も確認しない";
	protected $description = "何も確認しない";

	public function getMessage() {
		return "何も確認しない";
	}

	public function check() {
		return self::MATCHED;
	}
}