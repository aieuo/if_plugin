<?php

namespace aieuo\ip\condition;

use pocketmine\item\Item;

use aieuo\ip\utils\Language;
use pocketmine\Player;

class CanAddItem extends TypeItem {
    protected $id = self::CAN_ADD_ITEM;
    protected $name = "@condition.canadditem.name";
    protected $description = "@condition.canadditem.description";
    protected $detail = "condition.canadditem.detail";

    public function execute(Player $player): ?bool {
        $item = $this->getItem();
        if (!($item instanceof Item)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return null;
        }
        return $player->getInventory()->canAddItem($item);
    }
}