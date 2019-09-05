<?php

namespace aieuo\ip\condition;

use pocketmine\utils\TextFormat;
use pocketmine\Player;
use aieuo\ip\utils\Language;
use aieuo\ip\economy\Economy;

class LessMoney extends TypeMoney {
    protected $id = self::LESS_MONEY;
    protected $name = "@condition.lessmoney.name";
    protected $description = "@condition.lessmoney.description";
    protected $detail = "condition.lessmoney.detail";

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
        return $mymoney <= $this->getAmount();
    }
}