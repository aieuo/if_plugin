<?php

namespace aieuo\ip\processes;

use pocketmine\level\Position;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Teleport extends TypePosition
{
	public $id = self::TELEPORT;

	public function getName()
	{
		return "テレポートする";
	}

	public function getDescription()
	{
		return "§7<pos>§fにテレポートする";
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$pos = $this->getPosition();
		if(!($pos instanceof Position))
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
        $player->teleport($pos);
	}
}