<?php

namespace aieuo\ip\processes;

use pocketmine\event\Event;

use aieuo\ip\ifPlugin;
use aieuo\ip\variable\Variable;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class CooperationRepeat extends Process {

	protected $id = self::COOPERATION_REPEAT;
    protected $name = "ほかのIF指定した回数実行する";
    protected $description = "§7<name>§fという名前のIFを§7<count>§f回実行する";

	public function getMessage() {
		$name = $this->getCooperationName();
		$count = $this->getCount();
		return $name."を".$count."回実行する";
	}

	public function getCooperationName() {
		return $this->getValues()[0];
	}

	public function getCount() {
		return $this->getValues()[1];
	}

	public function setNames(string $name, int $count) {
		$this->setValues([$name, $count]);
	}

	public function parse(string $content) {
		$datas = explode(";", $content);
		if(!isset($datas[1])) return false;
		$count = array_pop($datas);
		$name = implode(";", $datas);
        return [$name, $count];
	}

	public function execute() {
		$player = $this->getPlayer();
        $manager = ifPlugin::getInstance()->getChainManager();
        if(!$manager->isAdded($this->getCooperationName())) {
        	$player->sendMessage("§c[".$this->getName()."] その名前の物は追加されていません");
        	return;
        }
        $datas = $manager->get($this->getCooperationName());
        $count = $this->getCount();
        for($i = 0; $i < $count; $i ++) {
            $options = [
                "player" => $player,
                "count" => $i,
            ];
            if($this->getEvent() instanceof Event) $options["event"] = $this->getEvent();
	        $manager->executeIfMatchCondition($player,
	            $datas["if"],
	            $datas["match"],
	            $datas["else"],
	            $options
	        );
	    }
	}

	public function getEditForm(string $default = "", string $mes = "") {
        $manager = ifPlugin::getInstance()->getChainManager();
        $names = $this->parse($default);
        $name = $default;
        $count = "";
        if($names === false and $default !== "") {
        	$mes .= "§c正しく入力できていません§f";
        } else {
        	$name = $names[0];
        	$count = $names[1];
        }
        if($default !== "" and !$manager->isAdded($name)) $mes .= "§eその名前のIFは追加されていません";
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<name>§f 名前を入力してください", "例) aieuo", $name),
                Elements::getInput("\n§7<count>§f 実行する回数を入力してください", "例) 5", $count),
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