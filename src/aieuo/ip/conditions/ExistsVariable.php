<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class ExistsVariable extends Condition {

	protected $id = self::EXISTS_VARIABLE;
    protected $name = "変数が存在するか";
	protected $description = "変数§7<variable>§fが存在するか";

	public function getMessage() {
		$name = $this->getVariableName();
		return $name."という名前の変数が存在するなら";
	}

	public function getVariableName() {
		return $this->getValues();
	}

	public function setVariableName(string $variable) {
		$this->setValues($variable);
	}

	public function check() {
		if(ifPlugin::getInstance()->getVariableHelper()->exists($this->getVariableName())) return self::MATCHED;
		return self::NOT_MATCHED;
	}


	public function getEditForm(string $default = "", string $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<variable>§f 変数の名前を入力してください", "例) aieuo", $default),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas){
    	$status = true;
    	if($datas[1] === "") $status = null;
    	return ["status" => $status, "contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}