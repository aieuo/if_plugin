<?php

namespace aieuo\ip\conditions;

class IsFlying extends Condition {

	protected $id = self::IS_FLYING;
	protected $name = "プレイヤーが飛んでいるか";
	protected $description = "プレイヤーが飛んでいるなら";

	public function getMessage() {
		return "プレイヤーが飛んでいるなら";
	}

	public function check() {
		$player = $this->getPlayer();
		return $player->isFlying() ? self::MATCHED : self::NOT_MATCHED;
	}
}