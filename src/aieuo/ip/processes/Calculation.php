<?php

namespace aieuo\ip\processes;

use aieuo\ip\IFPlugin;
use aieuo\ip\variable\Variable;
use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;
use aieuo\ip\variable\ListVariable;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class Calculation extends Process {

    protected $id = self::CALCULATION;
    protected $name = "@process.calculation.name";
    protected $description = "@process.calculation.description";

    const ERROR = -1;
    const ADDITION = 0;
    const SUBTRACTION = 1;
    const MULTIPLICATION = 2;
    const DIVISION = 3;
    const MODULO = 4;

    public function getDetail(): string {
        if ($this->getValues() === false) return false;
        $variable1 = $this->getValue1();
        $variable2 = $this->getValue2();
        for ($i = 1; $i <= 2; $i ++) {
            ${"value".$i} = ${"variable".$i}->getValue();

            if (${"variable".$i} instanceof StringVariable and is_numeric(${"value".$i})) {
                ${"value".$i} = "(str)".${"value".$i};
            } elseif (${"variable".$i} instanceof NumberVariable and !is_numeric(${"value".$i})) {
                ${"value".$i} = "(num)".${"value".$i};
            } elseif (${"variable".$i} instanceof ListVariable) {
                ${"value".$i} = ${"variable".$i}->toStringVariable()->getValue();
            }
        }
        $operator = $this->getOperator();
        switch ($operator) {
            case self::ADDITION:
                $mes = Language::get("process.calculation.detail.add", [$value1, $value2]);
                break;
            case self::SUBTRACTION:
                $mes = Language::get("process.calculation.detail.sub", [$value1, $value2]);
                break;
            case self::MULTIPLICATION:
                $mes = Language::get("process.calculation.detail.mul", [$value1, $value2]);
                break;
            case self::DIVISION:
                $mes = Language::get("process.calculation.detail.div", [$value1, $value2]);
                break;
            case self::MODULO:
                $mes = Language::get("process.calculation.detail.mod", [$value1, $value2]);
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

    public function getAssignName() {
        return $this->getValues()[3];
    }

    public function setNumbers(Variable $value1, Variable $value2, int $ope, string $assign = "result") {
        $this->setValues([$value1, $value2, $ope, $assign]);
    }

    public function parse(string $numbers) {
        if (!preg_match("/\s*(.+)\s*\[ope:([0-9])\]\s*(.+)\s*;\s*([^;]*)\s*$/", $numbers, $matches)) return false;
        $helper = IFPlugin::getInstance()->getVariableHelper();
        $operator = (int)$matches[2];
        $value1 = $matches[1];
        $value2 = $matches[3];
        $assign = $matches[4] === "" ? "result" : $matches[4];
        $type1 = $helper->getType($value1);
        $value1 = $helper->currentType($value1);
        $var1 = Variable::create("value1", $value1, $type1);
        $type2 = $helper->getType($value2);
        $value2 = $helper->currentType($value2);
        $var2 = Variable::create("value2", $value2, $type2);
        return [$var1, $var2, $operator, $assign];
    }

    public function execute() {
        $player = $this->getPlayer();
        if ($this->getValues() === false) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        $variable1 = $this->getValue1();
        $variable2 = $this->getValue2();
        $operator = $this->getOperator();
        $name = $this->getAssignName();
        switch ($operator) {
            case self::ADDITION:
                $result = $variable1->addition($variable2, $name);
                break;
            case self::SUBTRACTION:
                $result = $variable1->subtraction($variable2, $name);
                break;
            case self::MULTIPLICATION:
                $result = $variable1->multiplication($variable2, $name);
                break;
            case self::DIVISION:
                $result = $variable1->division($variable2, $name);
                break;
            case self::MODULO:
                $result = $variable1->modulo($variable2, $name);
                break;
            default:
                $player->sendMessage(Language::get("process.calculation.invalid", [$this->getName()]));
                return;
        }
        if ($result->getName() == "ERROR") {
            $player->sendMessage(Language::get("process.calculation.error"), [$this->getName(), $result->getValue()]);
            return;
        }
        IFPlugin::getInstance()->getVariableHelper()->add($result);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $values = $this->parse($default);
        $value0 = $default;
        $value1 = "";
        $operator = self::ADDITION;
        $name = "";
        if ($values !== false) {
            for ($i = 0; $i <= 1; $i ++) {
                ${"value".$i} = $values[$i]->getValue();

                if ($values[$i] instanceof StringVariable and is_numeric(${"value".$i})) {
                    ${"value".$i} = "(str)".${"value".$i};
                } elseif ($values[$i] instanceof NumberVariable and !is_numeric(${"value".$i})) {
                    ${"value".$i} = "(num)".${"value".$i};
                } elseif ($values[$i] instanceof ListVariable) {
                    ${"value".$i} = $values[$i]->toStringVariable()->getValue();
                }
            }
            $operator = $values[2];
            $name = $values[3];
        } elseif ($default !== "") {
            $mes .= Language::get("form.error");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.calculation.form.value1"), Language::get("input.example", ["100"]), $value0),
                Elements::getDropdown(Language::get("process.calculation.form.operator"), [
                    Language::get("process.calculation.form.operator.add"),
                    Language::get("process.calculation.form.operator.sub"),
                    Language::get("process.calculation.form.operator.mul"),
                    Language::get("process.calculation.form.operator.div"),
                    Language::get("process.calculation.form.operator.mod"),
                ], $operator),
                Elements::getInput(Language::get("process.calculation.form.value2"), Language::get("input.example", ["50"]), $value1),
                Elements::getInput(Language::get("process.calculation.form.result"), Language::get("input.example", ["aieuo"]), $name),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $data) {
        $status = true;
        $values_str = $data[1]."[ope:".$data[2]."]".$data[3].";".$data[4];
        if ($data[1] === "" or $data[2] === "" or $data[3] === "") {
            $status = null;
        } else {
            $values = $this->parse($values_str);
            if ($values === false) $status = false;
        }
        return ["status" => $status, "contents" => $values_str, "delete" => $data[5], "cancel" => $data[6]];
    }
}