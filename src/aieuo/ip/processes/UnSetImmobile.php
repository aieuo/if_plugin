<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class UnSetImmobile extends Process
{
	public $id = self::UNSET_IMMOBILE;

	public function getName()
	{
		return "動けるようにする";
	}

	public function getDescription()
	{
		return "プレイヤーを動けるようにする";
	}

	public function getMessage() {
		return "プレイヤーを動けるようにする";
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$player->setImmobile(false);
	}
}