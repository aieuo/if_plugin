<?php

namespace aieuo\ip\conditions;

use pocketmine\item\Item;

use aieuo\ip\utils\Language;

class InHand extends TypeItem {

	protected $id = self::IN_HAND;
    protected $name = "@condition.inhand.name";
    protected $description = "@condition.inhand.description";

	public function getMessage() {
		$item = $this->getItem();
		if(!($item instanceof Item)) return false;
		return Language::get("condition.isop.detail", [$item->getId(), $item->getDamage(), $item->getCount()]);
	}

	public function check() {
		$player = $this->getPlayer();
	    $item = $this->getItem();
	    if(!($item instanceof Item)) {
			$player->sendMessage(Language::get("input.invalid", [$this->getName()]));
			return self::ERROR;
		}
	    $hand = $player->getInventory()->getItemInHand();
        if(
        	$hand->getId() == $item->getId()
        	and $hand->getDamage() == $item->getDamage()
        	and $hand->getCount() >= $item->getCount()
        ) {
            return self::MATCHED;
        }
        return self::NOT_MATCHED;
	}
}