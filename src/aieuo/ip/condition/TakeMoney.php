<?php

namespace aieuo\ip\condition;

use pocketmine\utils\TextFormat;
use pocketmine\Player;
use aieuo\ip\utils\Language;
use aieuo\ip\economy\Economy;

class TakeMoney extends TypeMoney {
    protected $id = self::TAKE_MONEY;
    protected $name = "@condition.takemoney.name";
    protected $description = "@condition.takemoney.description";
    protected $detail = "condition.takemoney.detail";

    public function execute(Player $player): ?bool {
        if (!$this->isDataValid()) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return null;
        }
        if (!Economy::isPluginLoaded()) {
            $player->sendMessage(TextFormat::RED.Language::get("economy.notfound"));
            return null;
        }
        $economy = Economy::getPlugin();
        $mymoney = $economy->getMoney($player->getName());
        if ($mymoney >= $this->getAmount()) {
            $economy->takeMoney($player->getName(), $this->getAmount());
            return true;
        }
        return false;
    }
}