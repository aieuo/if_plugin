<?php

namespace aieuo\ip\variable;

use aieuo\ip\utils\Language;

class ListVariable extends Variable {

    public $type = Variable::LIST;

    public function getString() {
        return implode(", ", $this->getValue());
    }

    public function addition(Variable $var, string $name = "result") {
        if ($var->getType() !== Variable::LIST) {
            return new StringVariable("ERROR", Language::get("variable.list.add.error"));
        }
        $result = array_merge($this->getValue(), $var->getValue());
        return new ListVariable($name, $result);
    }

    public function subtraction(Variable $var, string $name = "result") {
        if ($var->getType() !== Variable::LIST) {
            return new StringVariable("ERROR", Language::get("variable.list.sub.error"));
        }
        $result = array_diff($this->getValue(), $var->getValue());
        $result = array_values($result);
        return new ListVariable($name, $result);
    }

    public function multiplication(Variable $var, string $name = "result") {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", Language::get("variable.list.mul.error"));
        }
        $result = [];
        $max = (int)$var->getValue();
        for ($i=0; $i<$max; $i ++) {
            $result = array_merge($result, $this->getValue());
        }
        return new ListVariable($name, $result);
    }

    public function division(Variable $var, string $name = "result") {
        return new StringVariable("ERROR", Language::get("variable.list.div.error"));
    }

    public function modulo(Variable $var, string $name = "result") {
        return new StringVariable("ERROR", Language::get("variable.list.mod.error"));
    }

    public function getValueFromIndex($index) {
        if (!isset($this->value[$index])) return null;
        return $this->value[$index];
    }

    public function getCount() {
        return count($this->value);
    }

    public function toStringVariable() {
        $variable = new StringVariable($this->getName(), "(list)".implode(",", $this->getValue()));
        return $variable;
    }
}