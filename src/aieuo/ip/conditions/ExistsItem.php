<?php

namespace aieuo\ip\conditions;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class ExistsItem extends TypeItem
{
	public $id = self::EXISTS_ITEM;

	public function getName()
	{
		return "インベントリに指定したアイテムが入ってるか";
	}

	public function getDescription()
	{
		return "インベントリにidが§7<id>§fのアイテムが§7<count>§f個以上あるなら";
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
        if($player->getInventory()->contains($item)) return self::MATCHED;
        return self::NOT_MATCHED;
	}
}