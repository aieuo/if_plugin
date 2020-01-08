<?php

namespace aieuo\ip\processes;

use pocketmine\Server;
use pocketmine\level\Position;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class TypePosition extends Process {

    public function getPosition() {
        return $this->getValues();
    }

    public function setPosition(string $pos) {
        $this->setValues($pos);
    }

    public function parse(string $pos) {
        if (!preg_match("/\s*(-?[0-9]+\.?[0-9]*)\s*,\s*(-?[0-9]+\.?[0-9]*)\s*,\s*(-?[0-9]+\.?[0-9]*)\s*,?\s*(.*)\s*/", $pos, $matches)) return false;
        if (empty($matches[4])) $matches[4] = "world";
        return new Position((float)$matches[1], (float)$matches[2], (float)$matches[3], Server::getInstance()->getLevelByName($matches[4]));
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $pos = $this->parse($default);
        if ($pos instanceof Position) {
            if ($pos->level === null) {
                $mes .= Language::get("process.position.level.notfound");
                $position = $default;
            } else {
                $position = $pos->x.",".$pos->y.",".$pos->z.",".$pos->level->getFolderName();
            }
        } else {
            if ($default !== "") $mes .= Language::get("form.error");
            $position = $default;
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.position.form.position"), Language::get("input.example", ["1,15,30,world"]), $position),
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
            $pos = $this->parse($data[1]);
            if ($pos === false) $status = false;
        }
        return ["status" => $status, "contents" => $data[1], "delete" => $data[2], "cancel" => $data[3]];
    }
}