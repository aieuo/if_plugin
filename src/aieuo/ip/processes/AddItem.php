<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class AddItem extends TypeItem
{
	public $id = self::ADD_ITEM;

	public function getName()
	{
		return "インベントリにアイテムを追加する";
	}

	public function getDescription()
	{
		return "インベントリにidが§7<id>§fの§7<name>§fという名前のアイテムを§7<count>§f追加する";
	}

	public function getMessage() {
		$item = $this->getItem();
		if(!($item instanceof Item)) return false;
		return "インベントリに(".$item->getId().":".$item->getDamage().",".$item->getName().")"."を".$item->getCount()."個追加する";
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$item = $this->getItem();
		if(!($item instanceof Item))
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
		$player->getInventory()->addItem($item);
	}
}