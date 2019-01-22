<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use aieuo\ip\variable\Variable;
use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;
use aieuo\ip\variable\ListVariable;

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

	public function __construct($player = null, $values = false)
	{
		parent::__construct($player);
		$this->setValues($values);
	}

	public function getName()
	{
		return "二つの値を計算する";
	}

	public function getDescription()
	{
		return "§7<value1>§fと§7<value2>§fを計算§7<opefator>§rした結果を{result}に入れる";
	}

	public function getMessage() {
		if($this->getValues() === false) return false;
		$variable1 = $this->getValue1();
		$variable2 = $this->getValue2();
		for($i = 1; $i <= 2; $i ++) {
			${"value".$i} = ${"variable".$i}->getValue();

			if(${"variable".$i} instanceof StringVariable and is_numeric(${"value".$i})) {
				${"value".$i} = "(str)".${"value".$i};
			} elseif(${"variable".$i} instanceof NumberVariable and !is_numeric(${"value".$i})) {
				${"value".$i} = "(num)".${"value".$i};
			} elseif(${"variable".$i} instanceof ListVariable) {
				${"value".$i} = "(list)".${"variable".$i}->toStringVariable()->getValue();
			}
		}
		$operator = $this->getOperator();
        switch ($operator){
            case self::ADDITION:
            	$mes = $value1."と".$value2."を足す";
                break;
            case self::SUBTRACTION:
            	$mes = $value1."から".$value2."を引く";
                break;
            case self::MULTIPLICATION:
            	$mes = $value1."と".$value2."を掛ける";
                break;
            case self::DIVISION:
            	$mes = $value1."を".$value2."で割る";
                break;
            case self::MODULO:
            	$mes = $value1."を".$value2."で割った余りを出す";
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

	public function getAssignName()
	{
		return $this->getValues()[3];
	}

	public function setNumbers(Variable $value1, Variable $value2, int $ope, string $assign = "result")
	{
		$this->setValues([$value1, $value2, $ope, $assign]);
	}

	public function parse(string $numbers)
	{
        if(!preg_match("/\s*(.+)\s*\[ope:([0-9])\]\s*(.+)\s*;\s*([^;]*)\s*$/", $numbers, $matches)) return false;
        $helper = ifPlugin::getInstance()->getVariableHelper();
        $operator = (int)$matches[2];
        $value1 = $matches[1];
        $value2 = $matches[3];
        $assign = $matches[4] === "" ? "result" : $matches[4];
        $type1 = $helper->getType($value1);
        $value1 = $helper->changeType($value1);
        $var1 = Variable::create("value1", $value1, $type1);
        $type2 = $helper->getType($value2);
        $value2 = $helper->changeType($value2);
        $var2 = Variable::create("value2", $value2, $type2);
        return [$var1, $var2, $operator, $assign];
	}

	public function execute()
	{
		if($this->getValues() === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
		$player = $this->getPlayer();
		$variable1 = $this->getValue1();
		$variable2 = $this->getValue2();
		$operator = $this->getOperator();
		$name = $this->getAssignName();
        switch ($operator){
            case self::ADDITION:
                $result = $variable1->Addition($variable2, $name);
                break;
            case self::SUBTRACTION:
                $result = $variable1->Subtraction($variable2, $name);
                break;
            case self::MULTIPLICATION:
                $result = $variable1->Multiplication($variable2, $name);
                break;
            case self::DIVISION:
                $result = $variable1->Division($variable2, $name);
                break;
            case self::MODULO:
                $result = $variable1->Modulo($variable2, $name);
                break;
            default:
                $player->sendMessage("§c[".$this->getName()."] その組み合わせは使用できません");
                return;
        }
        if($result->getName() == "ERROR") {
        	$player->sendMessage("§c[".$this->getName()."] ".$result->getValue());
        	return;
        }
        ifPlugin::getInstance()->getVariableHelper()->add($result);
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
		$values = $this->parse($default);
		$value0 = $default;
		$value1 = "";
		$operator = self::ADDITION;
		$name = "";
		if($values !== false)
		{
			for($i = 0; $i <= 1; $i ++) {
				${"value".$i} = $values[$i]->getValue();

				if($values[$i] instanceof StringVariable and is_numeric(${"value".$i})) {
					${"value".$i} = "(str)".${"value".$i};
				} elseif($values[$i] instanceof NumberVariable and !is_numeric(${"value".$i})) {
					${"value".$i} = "(num)".${"value".$i};
				} elseif($values[$i] instanceof ListVariable) {
					${"value".$i} = "(list)".$values[$i]->toStringVariable()->getValue();
				}
			}
			$operator = $values[2];
			$name = $values[3];
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
                Elements::getInput("\n§7<value1>§f 一つ目の値を入力してください", "例) 100", $value0),
                Elements::getDropdown("\n§7<operator>§f 選んでください", [
                	"一つ目の値と二つ目の値を足す (value1 + value2)",
                	"一つ目の値と二つ目の値を引く (value1 - value2)",
                	"一つ目の値と二つ目の値を掛ける (value1 * value2)",
                	"一つ目の値を二つ目で値を割る (value1 / value2)",
                	"一つ目の値を二つ目で値を割った余り (value1 % value2)",
                ], $operator),
                Elements::getInput("\n§7<value2>§f 二つ目の値を入力してください", "例) 50", $value1),
                Elements::getInput("\n§7<result>§f 結果を代入する変数の名前を入力してください(空白ならresult)", "例) aieuo", $name),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	$status = true;
    	$values_str = $datas[1]."[ope:".$datas[2]."]".$datas[3].";".$datas[4];
    	if($datas[1] === "" or $datas[2] === "" or $datas[3] === "") {
    		$status = null;
    	} else {
	    	$values = $this->parse($values_str);
	    	if($values === false) $status = false;
	    }
    	return ["status" => $status, "contents" => $values_str, "delete" => $datas[5], "cancel" => $datas[6]];
    }
}