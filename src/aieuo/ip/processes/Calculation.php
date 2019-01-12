<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use aieuo\ip\variable\Variable;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Calculation extends Process
{
	public $id = self::CALCULATION;

	const ERROR = -1;
	const ADDITION = 0;
	const SUBTRACTION = 1;
	const MULTIPLICATION = 2;
	const DIVISION = 3;
	const MODULO = 4;

	public function __construct($player = null, $value1 = 0, $value2 = 0, $operator = self::ADDITION)
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
		return "§7<value1>§fと§7<value2>§fを計算§7<opefator>§rした結果を{result}に入れる";
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

	public function toString() : string
	{
		$str = $this->getValue1()."[ope:".$this->getOperator()."]".$this->getValue2();
		return $str;
	}

	public function execute()
	{
		if($this->getValues() === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
		$player = $this->getPlayer();
		$value1 = $this->getValue1();
		$value2 = $this->getValue2();
		$operator = $this->getOperator();
        switch ($operator){
            case self::ADDITION:
                $result = (new Variable("value1", $value1))->Addition(new Variable("value2", $value2));
                break;
            case self::SUBTRACTION:
                $result = (new Variable("value1", $value1))->Subtraction(new Variable("value2", $value2));
                break;
            case self::MULTIPLICATION:
                $result = (new Variable("value1", $value1))->Multiplication(new Variable("value2", $value2));
                break;
            case self::DIVISION:
                $result = (new Variable("value1", $value1))->Division(new Variable("value2", $value2));
                break;
            case self::MODULO:
                $result = (new Variable("value1", $value1))->Modulo(new Variable("value2", $value2));
                break;
            default:
                $player->sendMessage("§c[".$this->getName()."] その組み合わせは使用できません");
                return;
        }
        ifPlugin::getInstance()->getVariableHelper()->add($result);
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
		$values = $this->parse($default);
		$value1 = $default;
		$value2 = "";
		$operator = self::ADD;
		if($values !== false)
		{
			$value1 = $values[0];
			$value2 = $values[1];
			$operator = $values[2];
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
                Elements::getInput("\n§7<value1>§f 一つ目の値を入力してください", "例) 100", $value1),
                Elements::getDropdown("\n§7<operator>§f 選んでください", [
                	"一つ目の値と二つ目の値を足す (value1 + value2)",
                	"一つ目の値と二つ目の値を引く (value1 - value2)",
                	"一つ目の値と二つ目の値を掛ける (value1 * value2)",
                	"一つ目の値を二つ目で値を割る (value1 / value2)",
                	"一つ目の値を二つ目で値を割った余り (value1 % value2)",
                ], $operator),
                Elements::getInput("\n§7<value2>§f 二つ目の値を入力してください", "例) 50", $value2),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	if($datas[1] === "" or $datas[2] === "" or $datas[3] === "") return null;
    	$values_str = $datas[1]."[ope:".$datas[2]."]".$datas[3];
    	$values = $this->parse($values_str);
    	if($values === false) return false;
    	return ["contents" => $values_str, "delete" => $datas[4], "cancel" => $datas[5]];
    }
}