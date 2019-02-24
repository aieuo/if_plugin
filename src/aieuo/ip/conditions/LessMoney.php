<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class LessMoney extends TypeMoney {

	protected $id = self::LESSMONEY;
    protected $name = "所持金が指定した金額以下か";
    protected $description = "所持金が§7<amount>§f以下なら";

	public function getMessage() {
		return "所持金が".$this->getAmount()."なら";
	}

	public function check() {
		$player = $this->getPlayer();
        $economy = ifPlugin::getInstance()->getEconomy();
        if($economy === null){
            $player->sendMessage("§c経済システムプラグインが見つかりません");
            return self::ERROR;
        }
        $mymoney = $economy->getMoney($player->getName());
        if($mymoney <= $this->getAmount()) return self::MATCHED;
        return self::NOT_MATCHED;
	}
}