<?php

namespace aieuo\ip\conditions;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Comparison extends Condition
{
	public $id = self::COMPARISON;

	const ERROR = -1;
	const EQUAL = 0;
	const NOT_EQUAL = 1;
	const GREATER = 2;
	const GREATER_EQUAL = 3;
	const LESS = 4;
	const LESS_EQUAL = 5;
	const CONTAINS = 6;
	const NOT_CONTAINS = 7;

	public function __construct($player = null, $value1 = 0, $value2 = 0, $operator = self::EQUAL)
	{
		parent::__construct($player);
		$this->setValues($value1, $value2, $operator);
	}

	public function getName()
	{
		return "二つの値を比較する";
	}

	public function getDescription()
	{
		return "§7<value1>§rと§7<value2>§rが§7<operator>§rなら";
	}

	public function getMessage() {
		if($this->getValues() === false) return false;
		$value1 = $this->getValue1();
		$value2 = $this->getValue2();
        switch ($this->getOperator()){
            case self::EQUAL:
                $mes = $value1."と".$value2."が等しいなら";
                break;
            case self::NOT_EQUAL:
                $mes = $value1."と".$value2."が等しくないから";
                break;
            case self::GREATER:
                $mes = $value1."より".$value2."が小さいなら";
                break;
            case self::LESS:
                $mes = $value1."より".$value2."が大きいなら";
                break;
            case self::GREATER_EQUAL:
                $mes = $value1."が".$value2."以上なら";
                break;
            case self::LESS_EQUAL:
                $mes = $value1."が".$value2."以下なら";
                break;
            case self::CONTAINS:
                $mes = $value1."の中に".$value2."が含まれているなら";
                break;
            case self::NOT_CONTAINS:
                $mes = $value1."の中に".$value2."が含まれていないなら";
                break;
            default:
                return false;
        }
        return $mes;
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
        if(!preg_match("/(.+)\[ope:([0-9])\](.+)/", $numbers, $matches)) return false;
        $operator = (int)$matches[2];
        $value1 = trim(rtrim($matches[1]));
        if(is_numeric($value1)) $value1 = (int)$value1;
        $value2 = trim(rtrim($matches[3]));
        if(is_numeric($value2)) $value2 = (int)$value2;
        return [$value1, $value2, $operator];
	}

	public function check()
	{
		$player = $this->getPlayer();
		if($this->getValues() === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return self::ERROR;
		}
		$value1 = $this->getValue1();
		$value2 = $this->getValue2();
		$result = self::NOT_MATCHED;
        switch ($this->getOperator()){
            case self::EQUAL:
                if($value1 == $value2) $result = self::MATCHED;
                break;
            case self::NOT_EQUAL:
                if($value1 != $value2) $result = self::MATCHED;
                break;
            case self::GREATER:
                if($value1 > $value2) $result = self::MATCHED;
                break;
            case self::LESS:
                if($value1 < $value2) $result = self::MATCHED;
                break;
            case self::GREATER_EQUAL:
                if($value1 >= $value2) $result = self::MATCHED;
                break;
            case self::LESS_EQUAL:
                if($value1 <= $value2) $result = self::MATCHED;
                break;
            case self::CONTAINS:
                if(strpos($value1, $value2) !== false) $result = self::MATCHED;
                break;
            case self::NOT_CONTAINS:
                if(strpos($value1, $value2) === false) $result = self::MATCHED;
                break;
            default:
                $player->sendMessage("§c[二つの値を比較する] 正しく入力できていません§r");
                break;
        }
        return $result;
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
		$values = $this->parse($default);
		$value1 = $default;
		$operator = self::EQUAL;
		$value2 = "";
		if($values !== false)
		{
			$value1 = $values[0];
			$operator = $values[2];
			$value2 = $values[1];
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
                	"二つの値が等しい (value1 == value2)",
                	"二つの値が等しくない (value1 != value2)",
                	"一つ目の値が二つ目の値より大きい (value1 > value2)",
                	"一つ目の値が二つ目の値より小さい (value1 < value2)",
                	"一つ目の値が二つ目の値以上 (value1 >= value2)",
                	"一つ目の値が二つ目の値以上 (value1 <= value2)",
                	"一つ目の値の中に二つ目の値が含まれている (value2 in value1)",
                	"一つ目の値の中に二つ目の値が含まれていない (value2 not in value1)",
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
    	$status = true;
    	if($datas[1] === "" or $datas[3] === "") {
    		$status = null;
    	} elseif($this->parse($datas[1]."[ope:".$datas[2]."]".$datas[3]) === false) {
    		$status = false;
    	}
    	return ["status" => $status, "contents" => $datas[1]."[ope:".$datas[2]."]".$datas[3], "delete" => $datas[4], "cancel" => $datas[5]];
    }
}