<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class SetHealth extends Process {

    protected $id = self::SET_HEALTH;
    protected $name = "@process.sethealth.name";
    protected $description = "@process.sethealth.description";

    public function getDetail(): string {
        $health = $this->getHealth();
        if ($health === false) return false;
        return Language::get("process.sethealth.detail", [$health]);
    }

    public function getHealth(): ?int {
        $health = $this->getValues();
        if (!is_int($health) or $health <= 0) return null;
        return $health;
    }

    public function setHealth(int $health) {
        $this->setValues($health);
    }

    public function parse(string $content) {
        $health = (int)$content;
        if ($health <= 0) return false;
        return $health;
    }

    public function execute() {
        $player = $this->getPlayer();
        $health = $this->gethealth();
        if ($health === false) {
            $player->sendMessage(Language::get("process.sethealth.zero", [$this->getName()]));
            return;
        }
        $player->setHealth($health);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $health = $this->parse($default);
        if ($health === false) {
            if ($default !== "") $mes = Language::get("process.sethealth.form.zero");
            $health = $default;
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.sethealth.form.health"), Language::get("input.example", ["10"]), $health),
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
            $health = $this->parse($datas[1]);
            if ($health === false) $status = false;
        }
        return ["status" => $status, "contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}