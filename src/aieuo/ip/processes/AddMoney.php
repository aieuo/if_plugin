<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class AddMoney extends TypeMoney
{
	public $id = self::ADDMONEY;

	public function getName()
	{
		return "所持金を増やす";
	}

	public function getDescription()
	{
		return "所持金を§7<amount>§f増やす";
	}

	public function getMessage() {
		return "所持金を".$this->getAmount()."増やす";
	}

	public function execute()
	{
		$player = $this->getPlayer();
    	$mymoney = ifPlugin::getInstance()->getEconomy()->getMoney($player->getName());
        if($mymoney === false){
            $player->sendMessage("§c経済システムプラグインが見つかりません");
            return;
        }
        ifPlugin::getInstance()->getEconomy()->addMoney($player->getName(), $this->getAmount());
	}
}