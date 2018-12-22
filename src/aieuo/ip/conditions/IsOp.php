<?php

namespace aieuo\ip\conditions;

class IsOp extends Condition
{
	public $id = self::IS_OP;

	public function getName()
	{
		return "プレイヤーがopか";
	}

	public function getDescription()
	{
		return "プレイヤーがopなら";
	}

	public function check()
	{
		$player = $this->getPlayer();
		return $player->isOp() ? self::MATCHED : self::NOT_MATCHED;
	}
}