<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetGamemode extends Process
{
	public $id = self::SET_GAMEMODE;

	public function __construct($player = null, $gamemode = false)
	{
		parent::__construct($player);
		$this->setValues($gamemode);
	}

	public function getName()
	{
		return "ゲームモードを変更する";
	}

	public function getDescription()
	{
		return "プレイヤーのゲームモードを§7<gamemode>§fにする";
	}

	public function getGamemode()
	{
		return $this->getValues();
	}

	public function setGamemode(int $gamemode)
	{
		$this->setValues($gamemode);
	}

	public function parse(string $content)
	{
		$gamemode = Server::getInstance()->getGamemodeFromString($content);
		if($gamemode === -1) return false;
		return $gamemode;
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$gamemode = $this->getGamemode();
		if($gamemode === false)
		{
			$player->sendMessage("§c[".$this->getName()."] ゲームモードが見つかりません");
			return;
		}
		$player->setGamemode($gamemode);
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
		$gamemode = $this->parse($default);
		if($gamemode === false)
		{
			if($default !== "") $mes .= "§cゲームモードが見つかりません§f";
			$gamemode = 0;
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getDropdown("\n§7<gamemode>§f ゲームモードを選択して下さい", ["サバイバル", "クリエイティブ", "アドベンチャー", "スペクテイター"], $gamemode),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}