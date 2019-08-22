<?php

namespace aieuo\ip\conditions;

use pocketmine\item\Item;

use aieuo\ip\utils\Language;

class CanAddItem extends TypeItem {

    protected $id = self::CAN_ADD_ITEM;
    protected $name = "@condition.canadditem.name";
    protected $description = "@condition.canadditem.description";

    public function getDetail(): string {
        $item = $this->getItem();
        if (!($item instanceof Item)) return false;
        return Language::get("condition.canadditem.detail", [$item->getId(), $item->getDamage(), $item->getName(), $item->getCount()]);
    }

    public function check() {
        $player = $this->getPlayer();
        $item = $this->getItem();
        if (!($item instanceof Item)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return self::ERROR;
        }
        if ($player->getInventory()->canAddItem($item)) return self::MATCHED;
        return self::NOT_MATCHED;
    }
}