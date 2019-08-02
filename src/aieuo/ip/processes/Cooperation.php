<?php

namespace aieuo\ip\processes;

use pocketmine\event\Event;

use aieuo\ip\ifPlugin;
use aieuo\ip\variable\Variable;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Cooperation extends Process {

	protected $id = self::COOPERATION;
    protected $name = "ほかのIFと連携する";
    protected $description = "§7<name>§fという名前のIFを実行する";

	public function getMessage() {
		$name = $this->getCooperationName();
		return $name."を実行する";
	}

	public function getCooperationName() {
		return $this->getValues();
	}

	public function setCooperationName(string $name) {
		$this->setValues($name);
	}

	public function execute() {
		$player = $this->getPlayer();
        $manager = ifPlugin::getInstance()->getChainManager();
        if(!$manager->isAdded($this->getCooperationName())) {
        	$player->sendMessage("§c[".$this->getName()."] その名前の物は追加されていません");
        	return;
        }
        $datas = $manager->get($this->getCooperationName());
        $options = [
            "player" => $player,
        ];
        if($this->getEvent() instanceof Event) $options["event"] = $this->getEvent();
        $manager->executeIfMatchCondition($player,
            $datas["if"],
            $datas["match"],
            $datas["else"],
            $options
        );
	}

	public function getEditForm(string $default = "", string $mes = "") {
        $manager = ifPlugin::getInstance()->getChainManager();
        if($default !== "" and !$manager->isAdded($default)) $mes .= "§eその名前のIFは追加されていません";
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<name>§f 名前を入力してください", "例) aieuo", $default),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	$status = true;
    	if($datas[1] === "") $status = null;
    	return ["status" => $status, "contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}