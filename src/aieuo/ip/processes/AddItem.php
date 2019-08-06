<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\utils\Language;

class AddItem extends TypeItem {

    protected $id = self::ADD_ITEM;
    protected $name = "@process.additem.name";
    protected $description = "@process.additem.description";

    public function getMessage() {
        $item = $this->getItem();
        if (!($item instanceof Item)) return false;
        return Language::get("process.additem.detail", [$item->getId(), $item->getDamage(), $item->getName(), $item->getCount()]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $item = $this->getItem();
        if (!($item instanceof Item)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        $player->getInventory()->addItem($item);
    }
}