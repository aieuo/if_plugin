<?php

namespace aieuo\ip\processes;

use aieuo\ip\utils\Language;
use aieuo\ip\ifPlugin;

class SetMoney extends TypeMoney {

    protected $id = self::SETMONEY;
    protected $name = "@process.setmoney.name";
    protected $description = "@process.setmoney.description";

    public function getMessage() {
        return Language::get("process.setmoney.detail", [$this->getAmount()]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $economy = ifPlugin::getInstance()->getEconomy();
        if ($economy === null) {
            $player->sendMessage(Language::get("economy.notfound"));
            return;
        }
        $economy->setMoney($player->getName(), $this->getAmount());
    }
}