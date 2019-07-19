<?php

namespace aieuo\ip\conditions;

use aieuo\ip\utils\Language;

class IsFlying extends Condition {

	protected $id = self::IS_FLYING;
	protected $name = "@condition.isflying.name";
	protected $description = "@condition.isflying.description";

	public function getMessage() {
		return Language::get("condition.isflying.detail");
	}

	public function check() {
		$player = $this->getPlayer();
		return $player->isFlying() ? self::MATCHED : self::NOT_MATCHED;
	}
}