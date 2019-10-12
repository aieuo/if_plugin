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
        $datas = explode("[min]", $areas);
        $axis = (int)$datas[0];
        if (!isset($datas[1])) return false;
        $datas = explode("[max]", $datas[1]);
        $min = $datas[0];
        if (!isset($datas[1])) return false;
        $max = $datas[1];
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
        $datas1 = explode("[min]", $default);
        $axis = (int)$datas1[0];
        $datas2 = explode("[max]", $datas1[1] ?? $default);
        $min = $datas2[0];
        $max = $datas2[1] ?? $datas1[1] ?? $default;
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

    public function parseFormData(array $datas) {
        $status = true;
        $contents = $datas[1]."[min]".$datas[2]."[max]".$datas[3];
        if ($datas[2] === "" and $datas[3] === "") {
            $status = null;
        } else {
            $areas = $this->parse($contents);
            if ($areas == false) $status = false;
        }
        return ["status" => $status, "contents" => $contents, "delete" => $datas[4], "cancel" => $datas[5]];
    }
}