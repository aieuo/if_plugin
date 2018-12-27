<?php

namespace aieuo\ip\conditions;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class InHand extends TypeItem
{
	public $id = self::IN_HAND;

	public function getName()
	{
		return "指定したアイテムを手に持ってるか";
	}

	public function getDescription()
	{
		return "idが§7<id>§fのアイテムを§7<count>§f個以上手に持っているなら";
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
	    $hand = $player->getInventory()->getItemInHand();
        if(
        	$hand->getId() == $item->getId()
        	and $hand->getDamage() == $item->getDamage()
        	and $hand->getCount() >= $item->getCount()
        )
        {
            return self::MATCHED;
        }
        return self::NOT_MATCHED;
	}
}