<?php

namespace aieuo\ip\processes;

use aieuo\ip\IFPlugin;
use aieuo\ip\variable\Variable;
use aieuo\ip\utils\Language;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class AddVariable extends Process {

    protected $id = self::ADD_VARIABLE;
    protected $name = "@process.addvariable.name";
    protected $description = "@process.addvariable.description";

    public function getDetail(): string {
        $variable = $this->getVariable();
        if ($variable === false) return false;
        return Language::get("process.addvariable.detail", [$variable->getName(), $variable->getString()]);
    }

    public function getVariable(): ?Variable {
        $variable = $this->getValues();
        if (!($variable instanceof Variable)) return null;
        return $variable;
    }

    public function setVariable(Variable $variable) {
        $this->setValues($variable);
    }

    public function parse(string $content) {
        $data = explode(";", $content);
        if (!isset($data[1]) or $data[1] === "") return false;
        $helper = IFPlugin::getInstance()->getVariableHelper();
        $value = $helper->currentType($data[1]);
        return Variable::create($data[0], $value, $helper->getType($data[1]));
    }

    public function execute() {
        $player = $this->getPlayer();
        $variable = $this->getVariable();
        if ($variable === false) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        IFPlugin::getInstance()->getVariableHelper()->add($variable);
    }


    public function getEditForm(string $default = "", string $mes = "") {
        $var = $this->parse($default);
        $name = $default;
        $value = "";
        if ($var instanceof Variable) {
            $name = $var->getName();
            $value = $var->getString();
            if (is_numeric($value) and $var->getType() === Variable::STRING) {
                $value = "(str)".$value;
            } elseif (!is_numeric($value) and $var->getType() === Variable::NUMBER) {
                $value = "(num)".$value;
            }
        } elseif ($default !== "") {
            $mes .= Language::get("form.error");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.addvariable.form.name"), Language::get("input.example", ["aieuo"]), $name),
                Elements::getInput(Language::get("process.addvariable.form.value"), Language::get("input.example", ["1000"]), $value),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $data) {
        $status = true;
        $var_str = $data[1].";".$data[2];
        if ($data[1] === "" or $data[2] === "") {
            $status = null;
        } else {
            $var = $this->parse($var_str);
            if ($var === false) $status = false;
        }
        return ["status" => $status, "contents" => $var_str, "delete" => $data[3], "cancel" => $data[4]];
    }
}