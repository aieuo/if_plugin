<?php

namespace aieuo\ip\conditions;

use aieuo\ip\IFPlugin;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class ExistsVariable extends Condition {

    protected $id = self::EXISTS_VARIABLE;
    protected $name = "@condition.existsvariable.name";
    protected $description = "@condition.existsvariable.description";

    public function getDetail(): string {
        $name = $this->getVariableName();
        return Language::get("condition.existsvariable.detail", [$name]);
    }

    public function getVariableName(): ?string {
        $name = $this->getValues();
        return is_string($name) ? $name : null;
    }

    public function setVariableName(string $variable) {
        $this->setValues($variable);
    }

    public function check() {
        if (IFPlugin::getInstance()->getVariableHelper()->exists($this->getVariableName())) return self::MATCHED;
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

    public function parseFormData(array $data){
        $status = true;
        if ($data[1] === "") $status = null;
        return ["status" => $status, "contents" => $data[1], "delete" => $data[2], "cancel" => $data[3]];
    }
}