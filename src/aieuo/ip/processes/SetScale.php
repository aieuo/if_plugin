<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class SetScale extends Process {

    protected $id = self::SET_SCALE;
    protected $name = "@process.setscale.name";
    protected $description = "@process.setscale.description";

    public function getDetail(): string {
        $scale = $this->getScale();
        if ($scale === false) return false;
        return Language::get("process.setscale.detail", [$this->getName()]);
    }

    public function getScale(): ?float {
        $scale = $this->getValues();
        if (!is_numeric($scale)) return null;
        return (float)$scale;
    }

    public function setScale(float $scale) {
        $this->setValues($scale);
    }

    public function parse(string $content) {
        $scale = (float)$content;
        if ($scale <= 0) return false;
        return $scale;
    }

    public function execute() {
        $player = $this->getPlayer();
        $scale = $this->getScale();
        if ($scale === null) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        $player->setScale($scale);
    }


    public function getEditForm(string $default = "", string $mes = "") {
        $scale = $this->parse($default);
        if ($scale === false and $default !== "") {
            $scale = $default;
            $mes .= Language::get("process.setscale.form.error");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.setscale.form.scale"), Language::get("input.example", ["2"]), $scale),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        if ($datas[1] === "") {
            $status = null;
        } else {
            $scale = $this->parse($datas[1]);
            if ($scale === false) $status = false;
        }
        return ["status" => $status, "contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}