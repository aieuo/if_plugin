<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieui\ip\form\Elements;

class TakeMoney extends Condition
{
	public $id = self::TAKEMONEY;

	public function __construct($player = null, $amount = 0)
	{
		parent::__construct($player);
		$this->setValues($amount);
	}

	public function getName()
	{
		return "お金を減らす";
	}

	public function getDescription()
	{
		return "§7<amount>§f払えるなら";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$money = $this->parse($defaults);
		if($money <= 0)
		{
			$money = $defaults;
			$mes = "§c1以上の数字を入力してください§f";
		}
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<amount>\n値段を入力してください", "例) 1000", $money),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $amount) : int
	{
		$amount = (int)mb_convert_kana($amount, "n");
		return $amount;
	}

	public function getAmount() : int
	{
		return $this->getValues()[0];
	}

	public function setAmount(int $amount)
	{
		$this->setValues($amount);
	}

	public function check()
	{
		$player = $this->getPlayer();
    	$mymoney = ifPlugin::getInstance()->getEconomy()->getMoney($player->getName());
        if($mymoney === false){
            $player->sendMessage("§c経済システムプラグインが見つかりません");
            return self::NOT_MATCHED;
        }
        if($mymoney >= $this->getAmount()){
            ifPlugin::getInstance()->getEconomy()->takeMoney($player->getName(), $this->getAmount());
            return self::MATCHED;
        }
        return self::NOT_MATCHED;
	}
}