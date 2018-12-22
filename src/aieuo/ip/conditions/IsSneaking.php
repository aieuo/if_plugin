<?php

namespace aieuo\ip\conditions;

class IsSneaking extends Condition
{
	public $id = self::IS_SNEAKING;

	public function getName()
	{
		return "プレイヤーがスニークしているか";
	}

	public function getDescription()
	{
		return "プレイヤーがスニークしているなら";
	}

	public function check()
	{
		$player = $this->getPlayer();
		return $player->isSneaking() ? self::MATCHED : self::NOT_MATCHED;
	}
}