<?php

namespace aieuo\ip\conditions;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class TypeMoney extends Condition {
    public function __construct($player = null, $amount = 0) {
        parent::__construct($player);
        $this->setValues($amount);
    }

    public function getAmount() {
        return $this->getValues();
    }

    public function setAmount(int $amount) {
        $this->setValues($amount);
    }

    public function parse(string $amount) {
        $amount = (int)mb_convert_kana($amount, "n");
        return $amount;
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $money = $this->parse($default);
        if ($money <= 0) $money = $default;
        if ($money <= 0 and $default !== "") $mes .= Language::get("condition.overmoney.zero");
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)."\n"),
                Elements::getInput(Language::get("condition.overmoney.form.amount"), Language::get("input.example", ["1000"]), $money),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        if ($datas[1] === "") $status = null;
        return ["status" => $status, "contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}