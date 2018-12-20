<?php

namespace aieuo\ip\ifs;

use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieui\ip\form\Elements;

class Comparison extends IFs
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

	/** @var int */
	private $value1;
	/** @var int */
	private $value2;
	/** @var int */
	private $ope;

	public function __construct($player = null, $value1 = 0, $operator = 1, $value2 = 0)
	{
		parent::__construct($player);

		$this->value1 = $value1;
		$this->value2 = $value2;
		$this->ope = $operator;
	}

	public function getName()
	{
		return "二つの値を比較する";
	}

	public function getDescription()
	{
		return "§7<value1>§rと§7<value2>§rが§7<operator>§rなら";
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
			$operator = self::EQUAL;
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
                	"二つの値が等しい (value1 == value2)",
                	"二つの値が等しくない (value1 != value2)",
                	"一つ目の値が二つ目の値より大きい (value1 > value2)",
                	"一つ目の値が二つ目の値より小さい (value1 < value2)",
                	"一つ目の値が二つ目の値以上 (value1 >= value2)",
                	"一つ目の値が二つ目の値以上 (value1 <= value2)",
                	"一つ目の値の中に二つ目の値が含まれている (value2 in value1)",
                	"一つ目の値の中に二つ目の値が含まれていない (value2 not in value1)",
                ], $operator),
                Elements::getInput("<value2>\n二つ目の値を入力してください", "例) 50", $value2),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $numbers) : Array
	{
        if(!preg_match("/(\.+)\[ope:([0-9])\](\.+)/", $content, $matches)) return false;
        $operator = (int)$matches[2];
        $value1 = trim(rtrim($matches[1]));
        if(is_numeric($value1)) $value1 = (int)$value1;
        $value2 = trim(rtrim($matches[3]));
        if(is_numeric($value2)) $value2 = (int)$value2;
        return [$value1, $value2, $ope];
	}

	public function getValues()
	{
		return [$this->value1, $this->value2, $this->ope];
	}

	public function setValues($value1, $value2, int $ope)
	{
		$this->value1 = $value1;
		$this->value2 = $value2;
		$this->ope = $ope;
	}

	public function check()
	{
		$player = $this->getPlayer();
		$values = $this->getValues();
		$result = self::NOT_MATCHED;
        switch ($values[2]){
            case self::EQUAL:
                if($values[0] == $values[1]) $result = self::MATCHED;
                break;
            case self::NOT_EQUAL:
                if($values[0] != $values[1]) $result = self::MATCHED;
                break;
            case self::GREATER:
                if($values[0] > $values[1]) $result = self::MATCHED;
                break;
            case self::LESS:
                if($values[0] < $values[1]) $result = self::MATCHED;
                break;
            case self::GREATER_EQUAL:
                if($values[0] >= $values[1]) $result = self::MATCHED;
                break;
            case self::LESS_EQUAL:
                if($values[0] <= $values[1]) $result = self::MATCHED;
                break;
            case self::CONTAINS:
                if(strpos($values[0], $values[1]) !== false) $result = self::MATCHED;
                break;
            case self::NOT_CONTAINS:
                if(strpos($values[0], $values[1]) === false) $result = self::MATCHED;
                break;
            default:
                $player->sendMessage("§c[二つの値を比較する] 正しく入力できていません§r");
                break;
        }
        return $result;
	}
}