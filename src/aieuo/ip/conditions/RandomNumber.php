<?php

namespace aieuo\ip\conditions;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class RandomNumber extends Condition {

    protected $id = self::RANDOM_NUMBER;
    protected $name = "@condition.randomnumber.name";
    protected $description = "@condition.randomnumber.description";

    public function getDetail(): string {
        if ($this->getValues() === false) return false;
        return Language::get("condition.randomnumber.detail", [$this->getMin(), $this->getMax(), $this->getCheck()]);
    }

    public function getMin() {
        return $this->getValues()[0];
    }

    public function getMax() {
        return $this->getValues()[1];
    }

    public function getCheck() {
        return $this->getValues()[2];
    }

    public function setNumbers(int $min, int $max, int $check) {
        $this->setValues([$min, $max, $check]);
    }

    public function parse(string $numbers) {
        if (!preg_match("/(-?[0-9]+),(-?[0-9]+),(-?[0-9]+)/", $numbers, $matches)) return false;
        $min = min((int)$matches[1], (int)$matches[2]);
        $max = max((int)$matches[1], (int)$matches[2]);
        return [$min, $max, (int)$matches[3]];
    }

    public function check() {
        $player = $this->getPlayer();
        if ($this->getValues() === false) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return self::ERROR;
        }
        $rand = mt_rand($this->getMin(), $this->getMax());
        if ($rand === $this->getCheck()) return self::MATCHED;
        return self::NOT_MATCHED;
    }


    public function getEditForm(string $default = "", string $mes = "") {
        $numbers = $this->parse($default);
        $min = $default;
        $max = "";
        $check = "";
        if ($numbers !== false) {
            $min = $numbers[0];
            $max = $numbers[1];
            $check = $numbers[2];
            if ($check > $max or $check < $min) {
                $mes .= Language::get("condition.randomnumber.form.warning", [$check, $min, $max]);
            }
        } elseif ($default !== "") {
            $mes .= Language::get("form.error");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("condition.randomnumber.form.min"), Language::get("input.example", ["1"]), $min),
                Elements::getInput(Language::get("condition.randomnumber.form.max"), Language::get("input.example", ["5"]), $max),
                Elements::getInput(Language::get("condition.randomnumber.form.check"), Language::get("input.example", ["3"]), $check),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $data) {
        $status = true;
        $num_str = $data[1].",".$data[2].",".$data[3];
        if ($data[1] === "" or $data[2] === "" or $data[3] === "") {
            $status = null;
        } else {
            $num = $this->parse($num_str);
            if ($num === false) $status = false;
        }
        return ["status" => $status, "contents" => $num_str, "delete" => $data[4], "cancel" => $data[5]];
    }
}