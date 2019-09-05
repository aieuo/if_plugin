<?php

namespace aieuo\ip\condition;

use pocketmine\utils\TextFormat;
use pocketmine\Player;
use aieuo\ip\utils\Language;
use aieuo\ip\economy\Economy;

class OverMoney extends TypeMoney {
    protected $id = self::OVER_MONEY;
    protected $name = "@condition.overmoney.name";
    protected $description = "@condition.overmoney.description";
    protected $detail = "condition.overmoney.detail";

    public function execute(Player $player): ?bool {
        if (!$this->isDataValid()) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return null;
        }
        if (!Economy::isPluginLoaded()) {
            $player->sendMessage(TextFormat::RED.Language::get("economy.notfound"));
            return null;
        }
        $mymoney = Economy::getPlugin()->getMoney($player->getName());
        return $mymoney >= $this->getAmount();
    }
}