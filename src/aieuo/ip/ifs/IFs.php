<?php

namespace aieuo\ip\ifs;

use pocketmine\Player;

use aieuo\ip\form\Form;
use aieui\ip\form\Elements;

class IFs implements IfIds
{
	const MATCHED = 0;
	const NOT_MATCHED = 1;
	const NOT_FOUND = 2;
	const ERROR = 3;

	/** @var int */
	public $id;

	/** @var Player */
	private $player;

	public function __construct($player = null)
	{
		$this->player = $player;
	}

	public static function get($id)
	{
		return IfFactory::get($id);
	}

	public function getId()
	{
		return $this->id;
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

	public function getName() { return ""; }

	public function getDescription() { return ""; }

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

	public function check()
	{
		return Ifs::NOT_MATCHED;
	}
}