<?php

namespace aieuo\ip\processes;

use aieuo\ip\utils\Language;
use aieuo\ip\IFPlugin;

class SetMoney extends TypeMoney {

    protected $id = self::SET_MONEY;
    protected $name = "@process.setmoney.name";
    protected $description = "@process.setmoney.description";

    public function getDetail(): string {
        return Language::get("process.setmoney.detail", [$this->getAmount()]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $economy = IFPlugin::getInstance()->getEconomy();
        if ($economy === null) {
            $player->sendMessage(Language::get("economy.notfound"));
            return;
        }
        $economy->setMoney($player->getName(), $this->getAmount());
    }
}