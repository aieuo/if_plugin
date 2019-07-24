<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;

use aieuo\ip\utils\Language;

class TakeMoney extends TypeMoney {

	protected $id = self::TAKEMONEY;
    protected $name = "@condition.takemoney.name";
    protected $description = "@condition.takemoney.description";

	public function getMessage() {
		return Language::get("condition.takemoney.detail", [$this->getAmount()]);
	}

	public function check() {
		$player = $this->getPlayer();
        $economy = ifPlugin::getInstance()->getEconomy();
        if($economy === null) {
            $player->sendMessage("§c".Language::get("economy.notfound"));
            return self::ERROR;
        }
        $mymoney = $economy->getMoney($player->getName());
        if($mymoney >= $this->getAmount()){
            $economy->takeMoney($player->getName(), $this->getAmount());
            return self::MATCHED;
        }
        return self::NOT_MATCHED;
	}
}