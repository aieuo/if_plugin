<?php

namespace aieuo\ip\processes;

use pocketmine\level\Position;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetSleeping extends TypePosition
{
	public $id = self::SET_SLEEPING;

	public function getName()
	{
		return "寝かせる";
	}

	public function getDescription()
	{
		return "プレイヤーを§7<pos>§fに寝かせる";
	}

	public function getMessage() {
		$pos = $this->getPosition();
		if($pos === false) return false;
		return $pos->__toString()."で寝る";
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
		$player->sleepOn($pos);
	}
}