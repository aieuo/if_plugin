<?php

namespace aieuo\ip\condition;

use pocketmine\item\Item;

use aieuo\ip\utils\Language;
use pocketmine\Player;

class InHand extends TypeItem {
    protected $id = self::IN_HAND;
    protected $name = "@condition.inhand.name";
    protected $description = "@condition.inhand.description";
    protected $detail = "condition.inhand.detail";

    public function execute(Player $player): ?bool {
        $item = $this->getItem();
        if (!($item instanceof Item)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return null;
        }
        $hand = $player->getInventory()->getItemInHand();
        return $hand->getId() === $item->getId()
            and $hand->getDamage() === $item->getDamage()
            and $hand->getCount() >= $item->getCount()
            and $hand->getName() === $item->getName();
    }
}