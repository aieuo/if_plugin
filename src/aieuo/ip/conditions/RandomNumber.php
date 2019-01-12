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
		$this->setValues([$min, $max, $check]);
	}

	public function getName()
	{
		return "乱数が指定したものだったら";
	}

	public function getDescription()
	{
		return "§7<min>§r～§7<max>§rの範囲で生成した乱数が§7<number>§7だったら";
	}

	public function getMin()
	{
		return $this->getValues()[0];
	}

	public function getMax()
	{
		return $this->getValues()[1];
	}

	public function getCheck()
	{
		return $this->getValues()[2];
	}

	public function setNumbers(int $min, int $max, int $check)
	{
		$this->setValues([$min, $max, $check]);
	}

	public function parse(string $numbers)
	{
        if(!preg_match("/(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)/", $numbers, $matches)) return false;
        $min = min((int)$matches[1], (int)$matches[2]);
        $max = max((int)$matches[1], (int)$matches[2]);
        return [$min, $max, (int)$matches[3]];
	}

	public function toString() : string
	{
		$str = $this->getMin().",".$this->getMax().",".$this->getCheck();
		return $str;
	}

	public function check()
	{
		$player = $this->getPlayer();
		if($this->getValues() === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません§f");
			return self::ERROR;
		}
        $rand = mt_rand($this->getMin(), $this->getMax());
        if($rand === $this->getCheck()) return self::MATCHED;
        return self::NOT_MATCHED;
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
		$numbers = $this->parse($default);
		$min = $default;
		$max = "";
		$check = "";
		if($numbers !== false)
		{
			$min = $numbers[0];
			$max = $numbers[1];
			$check = $numbers[2];
			if($check > $max or $check < $min)
			{
				$mes .= "§e指定した数".$check."は".$min."~".$max."の範囲の乱数で生成されることはありません§f";
			}
		}
		elseif($default !== "")
		{
			$mes .= "§c正しく入力できていません§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<min>§f 乱数の範囲の最小値を入力してください", "例) 1", $min),
                Elements::getInput("\n§7<max>§f 乱数の範囲の最大値を入力してください", "例) 5", $max),
                Elements::getInput("\n§7<check>§f 確認する数を入力してください", "例) 3", $check),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}