<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetImmobile extends Process
{
	public $id = self::SET_IMMOBILE;

	public function getName()
	{
		return "動けないようにする";
	}

	public function getDescription()
	{
		return "プレイヤーを動けないようにする";
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$player->setImmobile();
	}
}