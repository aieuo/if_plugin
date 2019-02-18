<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class TakeMoney extends TypeMoney {

	protected $id = self::TAKEMONEY;
    protected $name = "お金を減らす";
    protected $description = "お金を§7<amount>§f払えるなら";

	public function getMessage() {
		return "お金を".$this->getAmount()."払えるなら";
	}

	public function check() {
		$player = $this->getPlayer();
    	$mymoney = ifPlugin::getInstance()->getEconomy()->getMoney($player->getName());
        if($mymoney === false){
            $player->sendMessage("§c経済システムプラグインが見つかりません");
            return self::ERROR;
        }
        if($mymoney >= $this->getAmount()){
            ifPlugin::getInstance()->getEconomy()->takeMoney($player->getName(), $this->getAmount());
            return self::MATCHED;
        }
        return self::NOT_MATCHED;
	}
}