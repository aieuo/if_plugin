<?php

namespace aieuo\ip\processes;

use aieuo\ip\IFPlugin;

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

    public function getVariableName(): ?string {
        $name = $this->getValues();
        return is_string($name) ? $name : null;
    }

    public function setVariableName(string $variable) {
        $this->setValues($variable);
    }

    public function execute() {
        IFPlugin::getInstance()->getVariableHelper()->del($this->getVariableName());
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

    public function parseFormData(array $data) {
        $status = true;
        if ($data[1] === "") $status = null;
        return ["status" => $status, "contents" => $data[1], "delete" => $data[2], "cancel" => $data[3]];
    }
}