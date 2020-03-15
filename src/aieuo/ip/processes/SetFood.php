<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class SetFood extends Process {

    protected $id = self::SET_FOOD;
    protected $name = "@process.setFood.name";
    protected $description = "@process.setFood.description";

    public function getDetail(): string {
        $health = $this->getFood();
        if ($health === false) return false;
        return Language::get("process.setFood.detail", [$health]);
    }

    public function getFood(): ?int {
        $health = $this->getValues();
        if (!is_int($health) or $health <= 0) return null;
        return $health;
    }

    public function setFood(int $health) {
        $this->setValues($health);
    }

    public function parse(string $content) {
        return (int)$content;
    }

    public function execute() {
        $player = $this->getPlayer();
        $food = $this->getFood();
        $player->setFood($food);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $health = $this->parse($default);
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.setFood.form.food"), Language::get("input.example", ["10"]), $health),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        return Form::encodeJson($data);
    }

    public function parseFormData(array $data) {
        $status = true;
        if ($data[1] === "") {
            $status = null;
        } else {
            $health = $this->parse($data[1]);
            if ($health === false) $status = false;
        }
        return ["status" => $status, "contents" => $data[1], "delete" => $data[2], "cancel" => $data[3]];
    }
}