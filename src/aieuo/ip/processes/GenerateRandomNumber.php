<?php

namespace aieuo\ip\processes;

use aieuo\ip\IFPlugin;
use aieuo\ip\variable\Variable;
use aieuo\ip\utils\Language;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class GenerateRandomNumber extends Process {

    protected $id = self::GENERATE_RANDOM_NUMBER;
    protected $name = "@process.generateRandomNumber.name";
    protected $description = "@process.generateRandomNumber.description";

    public function getDetail(): string {
        $values = $this->getValues();
        if ($values === false) return false;
        return Language::get("process.generateRandomNumber.detail", [$this->getMin(), $this->getMax(), $this->getResultName()]);
    }

    public function getMin() {
        return $this->getValues()[0];
    }

    public function getMax() {
        return $this->getValues()[1];
    }

    public function getResultName() {
        return $this->getValues()[2];
    }

    public function parse(string $content) {
        $datas = explode("[max]", $content);
        $min = (int)$datas[0];
        if (!isset($datas[1])) return false;
        $datas = explode("[result]", $datas[1]);
        $max = (int)$datas[0];
        $result = empty($datas[1]) ? "result" : $datas[1];
        return [min($min, $max), max($min, $max), $result];
    }

    public function execute() {
        $player = $this->getPlayer();
        if ($this->getValues() === false) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        $number = mt_rand($this->getMin(), $this->getMax());
        $variable = Variable::create($this->getResultName(), $number);
        IFPlugin::getInstance()->getVariableHelper()->add($variable);
    }


    public function getEditForm(string $default = "", string $mes = "") {
        $datas = $this->parse($default);
        $datas1 = explode("[max]", $default);
        $min = $datas1[0];
        $datas2 = explode("[result]", $datas1[1] ?? $default);
        $max = $datas2[0];
        $result = $datas2[1] ?? $datas1[1] ?? $default;
        if ($datas !== false) {
            $min = $datas[0];
            $max = $datas[1];
            $result = $datas[2];
        } elseif ($default !== "") {
            $mes .= Language::get("form.error");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.generateRandomNumber.form.min"), Language::get("input.example", ["0"]), $min),
                Elements::getInput(Language::get("process.generateRandomNumber.form.max"), Language::get("input.example", ["9"]), $max),
                Elements::getInput(Language::get("process.generateRandomNumber.form.result"), Language::get("input.example", ["result"]), $result),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        $contents = $datas[1]."[max]".$datas[2]."[result]".$datas[3];
        if ($datas[1] === "" or $datas[2] === "") {
            $status = null;
        } else {
            $result = $this->parse($contents);
            if ($result === false) $status = false;
        }
        return ["status" => $status, "contents" => $contents, "delete" => $datas[4], "cancel" => $datas[5]];
    }
}