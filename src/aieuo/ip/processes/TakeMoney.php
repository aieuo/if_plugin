<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use aieuo\ip\utils\Language;

class TakeMoney extends TypeMoney {

    protected $id = self::TAKEMONEY;
    protected $name = "@process.takemoney.name";
    protected $description = "@process.takemoney.description";

    public function getMessage() {
        return Language::get("process.takemoney.detail", [$this->getAmount()]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $economy = ifPlugin::getInstance()->getEconomy();
        if ($economy === null) {
            $player->sendMessage(Language::get("economy.notfound"));
            return;
        }
        $economy->takeMoney($player->getName(), $this->getAmount());
    }
}