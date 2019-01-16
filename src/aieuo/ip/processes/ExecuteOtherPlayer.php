<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\ifPlugin;

use aieuo\ip\variable\Variable;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class ExecuteOtherPlayer extends Process {
	public $id = self::EXECUTE_OTHER_PLAYER;

	public function __construct($player = null, $names = false) {
		parent::__construct($player);
		$this->setValues($names);
	}

	public function getName() {
		return "ほかのプレイヤーとしてIFを実行する";
	}

	public function getDescription() {
		return "§7<player>§fに§7<name>§fという名前のIFを実行させる";
	}

	public function getMessage() {
		$cname = $this->getCooperationName();
		$pname = $this->getPlayerName();
		return $cname."を".$pname."に実行させる";
	}

	public function getCooperationName() {
		return $this->getValues()[0];
	}

	public function getPlayerName() {
		return $this->getValues()[1];
	}

	public function setNames(string $name, string $playerName) {
		$this->setValues([$name, $playerName]);
	}

	public function parse(string $content) {
		$datas = explode(";", $content);
		if(!isset($datas[1])) return false;
		$pname = array_pop($datas);
		$cname = implode(";", $datas);
        return [$cname, $pname];
	}

	public function execute() {
		$player = $this->getPlayer();
        $manager = ifPlugin::getInstance()->getChainManager();
        if(!$manager->isAdded($this->getCooperationName())) {
        	$player->sendMessage("§c[".$this->getName()."] その名前の物は追加されていません");
        	return;
        }
        $playerName = $this->getPlayerName();
        $target = Server::getInstance()->getPlayer($playerName);
        if($target === null) {
        	$player->sendMessage("§c[".$this->getName()."] ".$playerName."は今サーバーにいません");
        	return;
        }
        $datas = $manager->get($this->getCooperationName());
        $manager->executeIfMatchCondition($target,
            $datas["if"],
            $datas["match"],
            $datas["else"],
            [
            	"player" => $target,
            	"origin" => $player
            ]
        );
	}

	public function getEditForm(string $default = "", string $mes = "") {
        $manager = ifPlugin::getInstance()->getChainManager();
        $names = $this->parse($default);
        $cname = $default;
        $pname = "";
        if($names === false and $default !== "") {
        	$mes .= "§c正しく入力できていません§f";
        } else {
        	$cname = $names[0];
        	$pname = $names[1];
        }
        if($default !== "" and !$manager->isAdded($name)) $mes .= "§eその名前のIFは追加されていません§f";
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<name>§f 実行するIFの名前を", "例) aieuo", $cname),
                Elements::getInput("\n§7<player>§f プレイヤーの名前を", "例) aiueo421", $pname),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	$status = true;
    	$status = true;
    	if($datas[1] === "" or $datas[2] === "") {
    		$status = null;
    	} else {
	    	$names = $this->parse($datas[1].";".$datas[2]);
	    	if($names === false) $status = false;
	    }
    	return ["status" => $status, "contents" => $datas[1].";".$datas[2], "delete" => $datas[3], "cancel" => $datas[4]];
    }
}