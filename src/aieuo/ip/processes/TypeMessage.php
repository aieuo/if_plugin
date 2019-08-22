<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class TypeMessage extends Process {

    public function getMessage(): ?string {
        $message = $this->getValues();
        if (!is_string($message)) return null;
        return $message;
    }

    public function setSendMessage(string $message) {
        $this->setValues($message);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.message.form.message"), Language::get("input.example", ["aieuo"]), $default),
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