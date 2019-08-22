<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class SetNametag extends Process {

    protected $id = self::SET_NAMETAG;
    protected $name = "@process.setnametag.name";
    protected $description = "@process.setnametag.description";

    public function getDetail(): string {
        return Language::get("process.setnametag.detail", [$this->getChangeName()]);
    }

    public function getChangeName(): ?string {
        $name = $this->getValues();
        if (!is_string($name)) return null;
        return $name;
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
                Elements::getInput(Language::get("process.setnametag.form.name"), Language::get("input.example", ["aieuo"]), $default),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
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