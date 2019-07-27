<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class ExistsVariable extends Condition {

	protected $id = self::EXISTS_VARIABLE;
    protected $name = "@condition.existsvariable.name";
	protected $description = "@condition.existsvariable.description";

	public function getMessage() {
		$name = $this->getVariableName();
		return Language::get("condition.existsvariable.detail", [$name]);
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
                Elements::getInput(Language::get("condition.existsvariable.form.name"), Language::get("input.example", ["aieuo"]), $default),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
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