<?php

namespace aieuo\ip\conditions;

class IsSneaking extends Condition {

	protected $id = self::IS_SNEAKING;
	protected $name = "プレイヤーがスニークしているか";
	protected $description = "プレイヤーがスニークしているなら";

	public function getMessage() {
		return "プレイヤーがスニークしているなら";
	}

	public function check() {
		$player = $this->getPlayer();
		return $player->isSneaking() ? self::MATCHED : self::NOT_MATCHED;
	}
}