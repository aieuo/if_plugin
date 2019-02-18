<?php

namespace aieuo\ip\conditions;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class CanAddItem extends TypeItem {

	protected $id = self::CAN_ADD_ITEM;
	protected $name = "インベントリにアイテムを追加できるか";
	protected $description = "インベントリにidが§7<id>§fのアイテムを§7<count>§f個追加できるスペースがあるなら";

	public function getMessage() {
		$item = $this->getItem();
		if(!($item instanceof Item)) return false;
		return "インベントリに(".$item->getId().":".$item->getDamage().")"."を".$item->getCount()."個追加できるなら";
	}

	public function check() {
		$player = $this->getPlayer();
		$item = $this->getItem();
		if(!($item instanceof Item)) {
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return self::ERROR;
		}
        if($player->getInventory()->canAddItem($item)) return self::MATCHED;
        return self::NOT_MATCHED;
	}
}