<?php

namespace aieuo\ip\conditions;

use pocketmine\Player;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Condition implements ConditionIds
{
	const MATCHED = 0;
	const NOT_MATCHED = 1;
	const NOT_FOUND = 2;
	const ERROR = -1;

	/** @var int */
	public $id;

	/** @var Player */
	private $player;

	/** @var array */
	private $values = [];

	public function __construct($player = null)
	{
		$this->player = $player;
	}

	public static function get($id)
	{
		return ConditionFactory::get($id);
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return "";
	}

	public function getDescription()
	{
		return "";
	}

	public function parse(string $str)
	{
		return $str;
	}

	public function toString() : string {
		return "";
	}

	public function setPlayer(Player $player) : self
	{
		$this->player = $player;
		return $this;
	}

	public function getPlayer() : Player
	{
		return $this->player;
	}

	public function setValues($values) : self
	{
		$this->values = $values;
		return $this;
	}

	public function getValues()
	{
		return $this->values;
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas){
    	return ["status" => true, "contents" => "", "delete" => $datas[1], "cancel" => $datas[2]];
    }

	public function check()
	{
		return Ifs::NOT_FOUND;
	}
}