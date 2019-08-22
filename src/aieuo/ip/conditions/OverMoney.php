<?php

namespace aieuo\ip\conditions;

use aieuo\ip\IFPlugin;

use aieuo\ip\utils\Language;
use pocketmine\utils\TextFormat;

class OverMoney extends TypeMoney {

    protected $id = self::OVERMONEY;
    protected $name = "@condition.overmoney.name";
    protected $description = "@condition.overmoney.description";

    public function getDetail(): string {
        return Language::get("condition.overmoney.detail", [$this->getAmount()]);
    }

    public function check() {
        $player = $this->getPlayer();
        $economy = IFPlugin::getInstance()->getEconomy();
        if ($economy === null) {
            $player->sendMessage(TextFormat::RED.Language::get("economy.notfound"));
            return self::ERROR;
        }
        $mymoney = $economy->getMoney($player->getName());
        if ($mymoney >= $this->getAmount()) return self::MATCHED;
        return self::NOT_MATCHED;
    }
}