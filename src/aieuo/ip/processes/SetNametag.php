<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetNametag extends Process {

    protected $id = self::SET_NAMETAG;
    protected $name = "名前を変える";
    protected $description = "表示する名前を§7<name>§fに変える";

	public function getMessage() {
		return "表示する名前を".$this->getChangeName()."にする";
	}

	public function getChangeName() {
		return $this->getValues();
	}

	public function setChangeName(string $name) {
		$this->setValues($name);
	}

	public function execute() {
		$player = $this->getPlayer();
		$name = $this->getChangeName();
    	$player->setNametag($name);
    	$player->setDisplayName($name);
	}

	public function getEditForm(string $default = "", string $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<name>§f 変える名前を入力してください", "例) aieuo", $default),
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