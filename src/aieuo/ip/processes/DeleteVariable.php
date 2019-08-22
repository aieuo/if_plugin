<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class DeleteVariable extends Process {

    protected $id = self::DELETE_VARIABLE;
    protected $name = "@process.deletevariable.name";
    protected $description = "@process.deletevariable.description";

    public function getDetail(): string {
        return Language::get("process.deletevariable.detail", [$this->getVariableName()]);
    }

    public function getVariableName() {
        return $this->getValues();
    }

    public function setVariableName(string $variable) {
        $this->setValues($variable);
    }

    public function execute() {
        ifPlugin::getInstance()->getVariableHelper()->del($this->getVariableName());
    }


    public function getEditForm(string $default = "", string $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.deletevariable.form.name"), Language::get("input.example", ["aieuo"]), $default),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        if ($datas[1] === "") $status = null;
        return ["status" => $status, "contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}