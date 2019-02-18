<?php

namespace aieuo\ip\conditions;

class IsOp extends Condition {

	protected $id = self::IS_OP;
	protected $name = "プレイヤーがopか";
	protected $description = "プレイヤーがopなら";

	public function getMessage() {
		return "プレイヤーがopなら";
	}

	public function check() {
		$player = $this->getPlayer();
		return $player->isOp() ? self::MATCHED : self::NOT_MATCHED;
	}
}