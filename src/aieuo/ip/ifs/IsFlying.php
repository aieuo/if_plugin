<?php

namespace aieuo\ip\ifs;

class IsFlying extends IFs
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

	public function check()
	{
		$player = $this->getPlayer();
		return $player->isFlying() ? self::MATCHED : self::NOT_MATCHED;
	}
}