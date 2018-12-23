<?php

namespace aieuo\ip\conditions;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class RandomNumber extends Condition
{
	public $id = self::RANDOM_NUMBER;

	public function __construct($player = null, $min = 0, $max = PHP_INT_MAX, $check = 0)
	{
		parent::__construct($player);
		$this->setValues($min, $max, $check, true);
	}

	public function getName()
	{
		return "乱数が指定したものだったら";
	}

	public function getDescription()
	{
		return "§7<min>§r～§7<max>§rの範囲で生成した乱数が§7<number>§7だったら";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$numbers = $this->parse($defaults);
		if($numbers !== false)
		{
			$min = $numbers[0];
			$max = $numbers[1];
			$check = $numbers[2];
			if($check > $max and $check < $min)
			{
				$mes = "§c指定した数(".$check.")は(".$min."～".$max.")の範囲の乱数で生成されることはありません§f";
			}
		}
		else
		{
			$min = $defaults;
			$max = "";
			$check = "";
			$mes = "§c正しく入力できていません§f";
		}
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<min>\n乱数の範囲の最小値を入力してください", "例) 1", $min),
                Elements::getInput("<max>\n乱数の範囲の最大値を入力してください", "例) 5", $max),
                Elements::getInput("<check>\n確認する数を入力してください", "例) 3", $check),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $numbers) : Array
	{
        if(!preg_match("/(-?[0-9]+),(-?[0-9]+);(-?[0-9]+)/", $numbers, $matches)) return false;
        $min = min((int)$matches[1], (int)$matches[2]);
        $max = max((int)$matches[1], (int)$matches[2]);
        return [$min, $max, (int)$matches[3]];
	}

	public function getMin() : int
	{
		return $this->getValues()[0];
	}

	public function getMax() : int
	{
		return $this->getValues()[1];
	}

	public function getCheck() : int
	{
		return $this->getValues()[2];
	}

	public function setNumbers(int $min, int $max, int $check)
	{
		$this->setValues([$min, $max, $check]);
	}

	public function check()
	{
		$player = $this->getPlayer();
		if($this->getValues() === false)
		{
			$player->sendMessage("§c[乱数が指定したものだったら] 正しく入力できていません§f");
			return self::ERROR;
		}
        $rand = mt_rand($this->getMin(), $this->getMax());
        if($rand == $this->getCheck()) return self::MATCHED;
        return self::NOT_MATCHED;
	}
}