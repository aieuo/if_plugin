<?php

namespace aieuo\ip\conditions;

use aieuo\ip\utils\Language;

class IsOp extends Condition {

	protected $id = self::IS_OP;
	protected $name = "@condition.isop.name";
	protected $description = "@condition.isop.description";

	public function getMessage() {
		return Language::get("condition.isop.detali");
	}

	public function check() {
		$player = $this->getPlayer();
		return $player->isOp() ? self::MATCHED : self::NOT_MATCHED;
	}
}