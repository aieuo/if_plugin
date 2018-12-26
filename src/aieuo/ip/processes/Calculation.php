<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;
use aieuo\ip\variable\Variable;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Calsulation extends Process
{
	public $id = self::CALCULATION;

	const ERROR = -1;
	const ADD = 0;
	const SUB = 1;
	const MUL = 2;
	const DIV = 3;
	const MODULO = 4;

	public function __construct($player = null, $value1 = 0, $value2 = 0, $operator = self::ADD)
	{
		parent::__construct($player);
		$this->setValues($value1, $value2, $operator);
	}

	public function getName()
	{
		return "二つの値を計算する";
	}

	public function getDescription()
	{
		return "§7<value1>§rと§7<value2>§rを計算§7<operator>§rした結果を{result}に入れる";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$values = $this->parse($defaults);
		if($values !== false)
		{
			$value1 = $values[0];
			$operator = $values[2];
			$value2 = $values[1];
		}
		else
		{
			$mes = "§c正しく入力できていません§f";
			$value1 = $defaults;
			$operator = self::ADD;
			$value2 = "";
		}
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<value1>\n一つ目の値を入力してください", "例) 100", $value1),
                Elements::getDropdown("<operator>\n選んでください", [
                	"一つ目の値と二つ目の値を足す (value1 + value2)",
                	"一つ目の値と二つ目の値を引く (value1 - value2)",
                	"一つ目の値と二つ目の値を掛ける (value1 * value2)",
                	"一つ目の値を二つ目で値を割る (value1 / value2)",
                	"一つ目の値を二つ目で値を割った余り (value1 % value2)",
                ], $operator),
                Elements::getInput("<value2>\n二つ目の値を入力してください", "例) 50", $value2),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $numbers)
	{
        if(!preg_match("/\s*(.+)\s*\[ope:([0-9])\]\s*(.+)\s*/", $numbers, $matches)) return false;
        $operator = (int)$matches[2];
        $value1 = $matches[1];
        $value2 = $matches[3];
        if(is_numeric($value1)) $value1 = (int)$value1;
        if(is_numeric($value2)) $value2 = (int)$value2;
        return [$value1, $value2, $operator];
	}

	public function getValue1()
	{
		return $this->getValues()[0];
	}

	public function getValue2()
	{
		return $this->getValues()[1];
	}

	public function getOperator()
	{
		return $this->getValues()[2];
	}

	public function setNumbers($value1, $value2, int $ope)
	{
		$this->setValues([$value1, $value2, $ope]);
	}

	public function check()
	{
		if($this->getValues() === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
		$player = $this->getPlayer();
		$value1 = $this->getValue1();
		$value2 = $this->getValue2();
        switch ($operator){
            case self::ADD:
                $val = (new Variable("input", $val1))->Addition(new Variable("value", $val2));
                break;
            case self::SUB:
                $val = (new Variable("input", $val1))->Subtraction($val2);
                break;
            case self::MUL:
                $val = (new Variable("input", $val1))->Multiplication($val2);
                break;
            case self::DIV:
                $val = (new Variable("input", $val1))->Division($val2);
                break;
            case self::MODULO:
                $val = (new Variable("input", $val1))->Modulo($val2);
                break;
            default:
                $player->sendMessage("§c[".$this->getName()."] その組み合わせは使用できません");
                break;
        }
        ifPlugin::getInstance()->getVariableHelper()->add($val);
	}
}