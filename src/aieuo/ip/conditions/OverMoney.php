<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class OverMoney extends TypeMoney {

	protected $id = self::OVERMONEY;
    protected $name = "所持金が指定した金額以上か";
    protected $description = "所持金が§7<amount>§f以上なら";

	public function getMessage() {
		return "所持金が".$this->getAmount()."以上なら";
	}

	public function check() {
		$player = $this->getPlayer();
        $economy = ifPlugin::getInstance()->getEconomy();
        if($economy === null) {
            $player->sendMessage("§c経済システムプラグインが見つかりません");
            return self::ERROR;
        }
        $mymoney = $economy->getMoney($player->getName());
        if($mymoney >= $this->getAmount()) return self::MATCHED;
        return self::NOT_MATCHED;
	}
}