<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use aieuo\ip\variable\Variable;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class DeleteVariable extends Process {

	protected $id = self::DELETE_VARIABLE;
    protected $name = "変数を削除する";
    protected $description = "§7<name>§fという名前の変数を削除する";

	public function getMessage() {
		return $this->getVariableName()."という名前の変数を削除する";
	}

	public function getVariableName() {
		return $this->getValues();
	}

	public function setVariableName(string $variable) {
		$this->setValues($variable);
	}

	public function execute() {
		$player = $this->getPlayer();
        ifPlugin::getInstance()->getVariableHelper()->del($this->getVariableName());
	}


	public function getEditForm(string $default = "", string $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<name>§f 変数の名前を入力してください", "例) aieuo", $default),
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