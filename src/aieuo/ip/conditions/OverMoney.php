<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class OverMoney extends TypeMoney
{
	public $id = self::OVERMONEY;

	public function getName()
	{
		return "所持金が指定した金額以上か";
	}

	public function getDescription()
	{
		return "所持金が§7<amount>§f以上なら";
	}

	public function getMessage() {
		return "所持金が".$this->getAmount()."以上なら";
	}

	public function check()
	{
		$player = $this->getPlayer();
    	$mymoney = ifPlugin::getInstance()->getEconomy()->getMoney($player->getName());
        if($mymoney === false){
            $player->sendMessage("§c経済システムプラグインが見つかりません");
            return self::ERROR;
        }
        if($mymoney >= $this->getAmount()) return self::MATCHED;
        return self::NOT_MATCHED;
	}
}