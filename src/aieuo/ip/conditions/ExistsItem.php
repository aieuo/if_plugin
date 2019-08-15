<?php

namespace aieuo\ip\conditions;

use pocketmine\item\Item;

use aieuo\ip\utils\Language;

class ExistsItem extends TypeItem {

    protected $id = self::EXISTS_ITEM;
    protected $name = "@condition.existsitem.name";
    protected $description = "@condition.existsitem.description";

    public function getMessage() {
        $item = $this->getItem();
        if (!($item instanceof Item)) return false;
        return Language::get("condition.existsitem.detail", [$item->getId(), $item->getDamage(), $item->getName(), $item->getCount()]);
    }

    public function check() {
        $player = $this->getPlayer();
        $item = $this->getItem();
        if (!($item instanceof Item)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return self::ERROR;
        }
        if ($player->getInventory()->contains($item)) return self::MATCHED;
        return self::NOT_MATCHED;
    }
}