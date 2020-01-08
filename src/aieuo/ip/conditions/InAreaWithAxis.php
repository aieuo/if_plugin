<?php

namespace aieuo\ip\conditions;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class InAreaWithAxis extends Condition {

    protected $id = self::IN_AREA_AXIS;
    protected $name = "@condition.inAreaWithAxis.name";
    protected $description = "@condition.inAreaWithAxis.description";

    private $axes = ["x", "y", "z"];

    public function getDetail(): string {
        $areas = $this->getValues();
        if ($areas === false) return false;
        return Language::get("condition.inAreaWithAxis.detail", [$this->axes[$this->getAxis()], $this->getMin(), $this->getMax()]);
    }

    public function getAxis() {
        return $this->getValues()[0];
    }

    public function getMin() {
        return $this->getValues()[1];
    }

    public function getMax() {
        return $this->getValues()[2];
    }

    public function parse(string $areas) {
        $data = explode("[min]", $areas);
        $axis = (int)$data[0];
        if (!isset($data[1])) return false;
        $data = explode("[max]", $data[1]);
        $min = $data[0];
        if (!isset($data[1])) return false;
        $max = $data[1];
        return [$axis, $min == "" ? null : (float)$min, $max == "" ? null : (float)$max];
    }

    public function check() {
        $player = $this->getPlayer();
        $areas = $this->getValues();
        if ($areas === false) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return self::ERROR;
        }
        $axis = $this->axes[$areas[0]];
        $min = $areas[1];
        $max = $areas[2];
        $pos = $player->$axis;

        if (($min === null or $pos >= $min) and ($max === null or $pos <= $max)) {
            return self::MATCHED;
        }
        return self::NOT_MATCHED;
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $settings = $this->parse($default);
        $data1 = explode("[min]", $default);
        $axis = (int)$data1[0];
        $data2 = explode("[max]", $data1[1] ?? $default);
        $min = $data2[0];
        $max = $data2[1] ?? $data1[1] ?? $default;
        if ($settings !== false) {
            $axis = $settings[0];
            $min = (string)$settings[1];
            $max = (string)$settings[2];
        } elseif ($default !== "") {
            $mes .= Language::get("form.error");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getDropdown(
                    Language::get("condition.inAreaWithAxis.form.axis"),
                    array_map(function ($a) {
                        return $a.Language::get("condition.inAreaWithAxis.axis");
                    }, $this->axes),
                    $axis
                ),
                Elements::getInput(Language::get("condition.inAreaWithAxis.form.min"), "", $min),
                Elements::getInput(Language::get("condition.inAreaWithAxis.form.max"), "", $max),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $data) {
        $status = true;
        $contents = $data[1]."[min]".$data[2]."[max]".$data[3];
        if ($data[2] === "" and $data[3] === "") {
            $status = null;
        } else {
            $areas = $this->parse($contents);
            if ($areas == false) $status = false;
        }
        return ["status" => $status, "contents" => $contents, "delete" => $data[4], "cancel" => $data[5]];
    }
}