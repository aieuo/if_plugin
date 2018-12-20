<?php

namespace aieuo\ip\ifs;

class IsOp extends IFs
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