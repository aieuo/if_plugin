<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class TypeMoney extends Condition
{
	public function __construct($player = null, $amount = 0)
	{
		parent::__construct($player);
		$this->setValues($amount);
	}

	public function getAmount()
	{
		return $this->getValues();
	}

	public function setAmount(int $amount)
	{
		$this->setValues($amount);
	}

	public function parse(string $amount)
	{
		$amount = (int)mb_convert_kana($amount, "n");
		return $amount;
	}

	public function toString() : string
	{
		$str = (string)$this->getAmount();
		return $str;
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
		$money = $this->parse($default);
		if($money <= 0) $money = $default;
		if($money <= 0 and $default !== "") $mes .= "§e1以上の数字を入力することを推奨します§f";
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)."\n"),
                Elements::getInput("\n§7<amount>§f 値段を入力してください", "例) 1000", $money),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}