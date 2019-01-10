<?php

namespace aieuo\ip\conditions;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class CanAddItem extends TypeItem
{
	public $id = self::CAN_ADD_ITEM;

	public function getName()
	{
		return "インベントリにアイテムを追加できるか";
	}

	public function getDescription()
	{
		return "インベントリにidが§7<id>§fのアイテムを§7<count>§f個追加できるスペースがあるなら";
	}

	public function check()
	{
		$player = $this->getPlayer();
		$item = $this->getItem();
		if(!($item instanceof Item))
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return self::ERROR;
		}
        if($player->getInventory()->canAddItem($item)) return self::MATCHED;
        return self::NOT_MATCHED;
	}
}