<?php

namespace aieuo\ip\processes;

use aieuo\ip\utils\Language;
use aieuo\ip\IFPlugin;

class AddMoney extends TypeMoney {

    protected $id = self::ADDMONEY;
    protected $name = "@process.addmoney.name";
    protected $description = "@process.addmoney.description";

    public function getDetail(): string {
        return Language::get("process.addmoney.detail", [$this->getAmount()]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $economy = IFPlugin::getInstance()->getEconomy();
        if ($economy === null) {
            $player->sendMessage(Language::get("economy.notfound"));
            return;
        }
        $economy->addMoney($player->getName(), $this->getAmount());
    }
}