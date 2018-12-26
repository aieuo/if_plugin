<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetGamemode extends Process
{
	public $id = self::SET_GAMEMODE;

	public function __construct($player = null, $pos = null)
	{
		parent::__construct($player);
		$this->setValues($pos);
	}

	public function getName()
	{
		return "ゲームモードを変更する";
	}

	public function getDescription()
	{
		return "プレイヤーのゲームモードを§7<gamemode>§fにする";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$gamemode = $this->parse($defaults);
		if($gamemode === false)
		{
			$mes = "§cゲームモードが見つかりません§f";
			$gamemode = 0;
		}
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

	public function parse(string $content)
	{
		$gamemode = Server::getInstance()->getGamemodeFromString($content);
		if($gamemode === -1) return false;
		return $gamemode;
	}

	public function getGamemode() : ?int
	{
		return $this->getValues();
	}

	public function setGamemode(int $gamemode)
	{
		$this->setValues($gamemode);
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$gamemode = $this->getGamemode();
		if($gamemode === false)
		{
			$player->sendMessage("§cゲームモードが見つかりません");
			return;
		}
		$player->setGamemode($gamemode);
	}
}