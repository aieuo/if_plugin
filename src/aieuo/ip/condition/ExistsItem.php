<?php

namespace aieuo\ip\condition;

use pocketmine\item\Item;

use aieuo\ip\utils\Language;
use pocketmine\Player;

class ExistsItem extends TypeItem {
    protected $id = self::EXISTS_ITEM;
    protected $name = "@condition.existsitem.name";
    protected $description = "@condition.existsitem.description";
    protected $detail = "condition.existsitem.detail";

    public function execute(Player $player): ?bool {
        $item = $this->getItem();
        if (!($item instanceof Item)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return null;
        }
        return $player->getInventory()->contains($item);
    }
}