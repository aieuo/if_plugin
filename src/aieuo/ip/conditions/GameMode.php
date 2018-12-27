<?php

namespace aieuo\ip\conditions;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class GameMode extends Condition
{
	public $id = self::GAMEMODE;

	public function __construct($player = null, $gamemode = false)
	{
		parent::__construct($player);
		$this->setValues($gamemode);
	}

	public function getName()
	{
		return "ゲームモードが指定したものだったら";
	}

	public function getDescription()
	{
		return "プレーヤーのゲームモードが§7<gamemode>§fだったら";
	}

	public function getGamemode()
	{
		return $this->getValues();
	}

	public function setGamemode(int $gamemode)
	{
		$this->setValues($gamemode);
	}

	public function parse(string $gamemode)
	{
		$intGamemode = Server::getInstance()->getGamemodeFromString($gamemode);
		if($intGamemode === -1) return false;
		return $intGamemode;
	}

	public function check()
	{
		$player = $this->getPlayer();
		$gamemode = $this->getGamemode();
		if($gamemode === false)
		{
			$player->sendMessage("§c[".$this->getName()."] ゲームモードが見つかりません");
			return self::ERROR;
		}
        return $player->getGamemode() === $gamemode ? self::MATCHED : self::NOT_MATCHED;
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
		if($default === "")
		{
			$gamemode = 0;
		}
		elseif(($gamemode = $this->parse($default)) === false)
		{
			$mes .= "§cゲームモードが見つかりません§f";
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