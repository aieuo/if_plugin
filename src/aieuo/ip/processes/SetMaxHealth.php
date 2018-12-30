<?php

namespace aieuo\ip\processes;

use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetMaxHealth extends SetHealth
{
	public $id = self::SET_MAXHEALTH;

	public function getName()
	{
		return "最大体力を変更する";
	}

	public function getDescription()
	{
		return "プレイヤーの最大体力を§7<health>§fにする";
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$health = $this->getHealth();
		if($health === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 最大体力は1以上にしてください");
			return;
		}
		$player->setMaxHealth($health);
	}
}