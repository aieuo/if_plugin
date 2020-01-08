<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class TypeCommand extends Process {

    public function getCommand(): ?string {
        $command = $this->getValues();
        if (!is_string($command)) return null;
        return $command;
    }

    public function setCommand(string $command) {
        $this->setValues($command);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.command.form.command"), Language::get("input.example", ["help"]), $default),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $data) {
        $status = true;
        if ($data[1] === "") {
            $status = null;
        } else {
            $command = $this->parse($data[1]);
            if ($command === false) $status = false;
        }
        return ["status" => $status, "contents" => $data[1], "delete" => $data[2], "cancel" => $data[3]];
    }
}