<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;

use aieuo\ip\utils\Language;
use pocketmine\utils\TextFormat;

class LessMoney extends TypeMoney {

    protected $id = self::LESSMONEY;
    protected $name = "@condition.lessmoney.name";
    protected $description = "@condition.lessmoney.description";

    public function getDetail(): string {
        return Language::get("condition.lessmoney.detail", [$this->getAmount()]);
    }

    public function check() {
        $player = $this->getPlayer();
        $economy = ifPlugin::getInstance()->getEconomy();
        if ($economy === null) {
            $player->sendMessage(TextFormat::RED.Language::get("economy.notfound"));
            return self::ERROR;
        }
        $mymoney = $economy->getMoney($player->getName());
        if ($mymoney <= $this->getAmount()) return self::MATCHED;
        return self::NOT_MATCHED;
    }
}