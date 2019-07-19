<?php

namespace aieuo\ip\conditions;

use aieuo\ip\utils\Language;

class IsSneaking extends Condition {

	protected $id = self::IS_SNEAKING;
	protected $name = "@condition.issneaking.name";
	protected $description = "@condition.issneaking.description";

	public function getMessage() {
		return Language::get("condition.issneaking.detail");
	}

	public function check() {
		$player = $this->getPlayer();
		return $player->isSneaking() ? self::MATCHED : self::NOT_MATCHED;
	}
}