<?php

namespace aieuo\ip\processes;

use pocketmine\Player;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Process implements ProcessIds
{

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
		return ProcessFactory::get($id);
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
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
    	return ["contents" => "", "delete" => $datas[1], "cancel" => $datas[2]];
    }

	public function execute()
	{
	}
}