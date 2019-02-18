<?php

namespace aieuo\ip\conditions;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class GameMode extends Condition {

	protected $id = self::GAMEMODE;
    protected $name = "ゲームモードが指定したものだったら";
    protected $description = "プレーヤーのゲームモードが§7<gamemode>§fだったら";

	public function getMessage() {
		$gamemode = $this->getGamemode();
		if($gamemode === false) return false;
		return "ゲームモードが".$gamemode."なら";
	}

	public function getGamemode() {
		return $this->getValues();
	}

	public function setGamemode(int $gamemode) {
		$this->setValues($gamemode);
	}

	public function parse(string $gamemode) {
		$intGamemode = Server::getInstance()->getGamemodeFromString($gamemode);
		if($intGamemode === -1) return false;
		return $intGamemode;
	}

	public function check() {
		$player = $this->getPlayer();
		$gamemode = $this->getGamemode();
		if($gamemode === false) {
			$player->sendMessage("§c[".$this->getName()."] ゲームモードが見つかりません");
			return self::ERROR;
		}
        return $player->getGamemode() === $gamemode ? self::MATCHED : self::NOT_MATCHED;
	}


	public function getEditForm(string $default = "", string $mes = "") {
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
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	$status = true;
    	if($datas[1] === "") {
    		$status = null;
    	} else {
	    	$gamemode = $this->parse((string)$datas[1]);
	    	if($gamemode === false) $status = false;
	    }
    	return ["status" => $status, "contents" => (string)$datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}