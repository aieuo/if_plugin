<?php

namespace aieuo\ip\conditions;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class Comparison extends Condition {

    protected $id = self::COMPARISON;
    protected $name = "@condition.comparison.name";
    protected $description = "@condition.comparison.description";

    const ERROR = -1;
    const EQUAL = 0;
    const NOT_EQUAL = 1;
    const GREATER = 2;
    const LESS = 3;
    const GREATER_EQUAL = 4;
    const LESS_EQUAL = 5;
    const CONTAINS = 6;
    const NOT_CONTAINS = 7;

    public function getDetail(): string {
        if ($this->getValues() === false) return false;
        $value1 = $this->getValue1();
        $value2 = $this->getValue2();
        switch ($this->getOperator()) {
            case self::EQUAL:
                $mes = Language::get("condition.comparison.detail.equal", [$value1, $value2]);
                break;
            case self::NOT_EQUAL:
                $mes = Language::get("condition.comparison.detail.not_equal", [$value1, $value2]);
                break;
            case self::GREATER:
                $mes = Language::get("condition.comparison.detail.greater", [$value1, $value2]);
                break;
            case self::LESS:
                $mes = Language::get("condition.comparison.detail.less", [$value1, $value2]);
                break;
            case self::GREATER_EQUAL:
                $mes = Language::get("condition.comparison.detail.greater_equal", [$value1, $value2]);
                break;
            case self::LESS_EQUAL:
                $mes = Language::get("condition.comparison.detail.less_equal", [$value1, $value2]);
                break;
            case self::CONTAINS:
                $mes = Language::get("condition.comparison.detail.contains", [$value1, $value2]);
                break;
            case self::NOT_CONTAINS:
                $mes = Language::get("condition.comparison.detail.not_contains", [$value1, $value2]);
                break;
            default:
                return false;
        }
        return $mes;
    }

    public function getValue1() {
        return $this->getValues()[0];
    }

    public function getValue2() {
        return $this->getValues()[1];
    }

    public function getOperator() {
        return $this->getValues()[2];
    }

    public function setNumbers($value1, $value2, int $ope) {
        $this->setValues([$value1, $value2, $ope]);
    }

    public function parse(string $numbers) {
        if (!preg_match("/(.*)\[ope:([0-9])\](.*)/", $numbers, $matches)) return false;
        $operator = (int)$matches[2];
        $value1 = trim(rtrim($matches[1]));
        if (is_numeric($value1)) $value1 = (int)$value1;
        $value2 = trim(rtrim($matches[3]));
        if (is_numeric($value2)) $value2 = (int)$value2;
        return [$value1, $value2, $operator];
    }

    public function check() {
        $player = $this->getPlayer();
        if ($this->getValues() === false) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return self::ERROR;
        }
        $value1 = $this->getValue1();
        $value2 = $this->getValue2();
        $result = self::NOT_MATCHED;
        switch ($this->getOperator()) {
            case self::EQUAL:
                if ($value1 == $value2) $result = self::MATCHED;
                break;
            case self::NOT_EQUAL:
                if ($value1 != $value2) $result = self::MATCHED;
                break;
            case self::GREATER:
                if ($value1 > $value2) $result = self::MATCHED;
                break;
            case self::LESS:
                if ($value1 < $value2) $result = self::MATCHED;
                break;
            case self::GREATER_EQUAL:
                if ($value1 >= $value2) $result = self::MATCHED;
                break;
            case self::LESS_EQUAL:
                if ($value1 <= $value2) $result = self::MATCHED;
                break;
            case self::CONTAINS:
                if (strpos($value1, $value2) !== false) $result = self::MATCHED;
                break;
            case self::NOT_CONTAINS:
                if (strpos($value1, $value2) === false) $result = self::MATCHED;
                break;
            default:
                $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
                break;
        }
        return $result;
    }


    public function getEditForm(string $default = "", string $mes = "") {
        $values = $this->parse($default);
        $value1 = $default;
        $operator = self::EQUAL;
        $value2 = "";
        if ($values !== false) {
            $value1 = $values[0];
            $operator = $values[2];
            $value2 = $values[1];
        } elseif ($default !== "") {
            $mes .= Language::get("form.error");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("condition.comparison.form.value1"), Language::get("input.example", ["100"]), $value1),
                Elements::getDropdown(Language::get("condition.comparison.form.operator"), [
                    Language::get("condition.comparison.form.equal"),
                    Language::get("condition.comparison.form.not_equal"),
                    Language::get("condition.comparison.form.greater"),
                    Language::get("condition.comparison.form.less"),
                    Language::get("condition.comparison.form.greater_equal"),
                    Language::get("condition.comparison.form.less_equal"),
                    Language::get("condition.comparison.form.contains"),
                    Language::get("condition.comparison.form.not_contains"),
                ], $operator),
                Elements::getInput(Language::get("condition.comparison.form.value2"), Language::get("input.example", ["100"]), $value2),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        if ($datas[1] === "" or $datas[3] === "") {
            $status = null;
        } elseif ($this->parse($datas[1]."[ope:".$datas[2]."]".$datas[3]) === false) {
            $status = false;
        }
        $contents = $datas[1]."[ope:".$datas[2]."]".$datas[3];
        return ["status" => $status, "contents" => $contents, "delete" => $datas[4], "cancel" => $datas[5]];
    }
}