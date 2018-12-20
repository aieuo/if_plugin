<?php

namespace aieuo\ip\ifs;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieui\ip\form\Elements;

class GameMode extends IFs
{
	public $id = self::GAMEMODE;

	/** @var int */
	private $gamemode;

	public function __construct($player = null, $gamemode = 0)
	{
		parent::__construct($player);
		$this->gamemode = $gamemode;
	}

	public function getName()
	{
		return "ゲームモードが指定したものだったら";
	}

	public function getDescription()
	{
		return "プレーヤーのゲームモードが§7<gamemode>§fだったら";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$gamemode = $this->parse($defaults);
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getDropdown("<gamemode>\nゲームモードを選択して下さい", ["サバイバル", "クリエイティブ", "アドベンチャー", "スペクテイター"], $gamemode),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $gamemode) : int
	{
		$intGamemode = 	Server::getInstance()->getGamemodeFromString($gamemode);
		return $intGamemode;
	}

	public function getGamemode() : int
	{
		return $this->gamemode;
	}

	/**
	 * @param int $gamemode
	 */
	public function setGamemode(int $gamemode)
	{
		$this->gamemode = $gamemode;
	}

	public function check()
	{
        return $player->getGamemode() == $this->getGamemode() ? self::MATCHED : self::NOT_MATCHED;
	}
}