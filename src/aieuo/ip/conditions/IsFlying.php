<?php

namespace aieuo\ip\conditions;

class IsFlying extends Condition
{
	public $id = self::IS_FLYING;

	public function getName()
	{
		return "プレイヤーが飛んでいるか";
	}

	public function getDescription()
	{
		return "プレイヤーが飛んでいるなら";
	}

	public function getMessage() {
		return "プレイヤーが飛んでいるなら";
	}

	public function check()
	{
		$player = $this->getPlayer();
		return $player->isFlying() ? self::MATCHED : self::NOT_MATCHED;
	}
}