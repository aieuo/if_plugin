<?php

namespace aieuo\ip\variable;

class NumberVariable extends Variable {

    public $type = Variable::NUMBER;

    public function getString() {
        return (string)$this->getValue();
    }

    public function addition(Variable $var, string $resultname = "result") {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", "数字に文字列を足すことはできません");
        }
        $result = $this->getValue() + $var->getValue();
        return new NumberVariable($resultname, $result);
    }

    public function subtraction(Variable $var, string $resultname = "result") {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", "数字から文字列を引くことはできません");
        }
        $result = $this->getValue() - $var->getValue();
        return new NumberVariable($resultname, $result);
    }

    public function multiplication(Variable $var, string $resultname = "result") {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", "数字に文字列を掛けることはできません");
        }
        $result = $this->getValue() * $var->getValue();
        return new NumberVariable($resultname, $result);
    }

    public function division(Variable $var, string $resultname = "result") {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", "数字を文字列で割ることはできません");
        }
        if ($var->getValue() === 0) {
            return new StringVariable("ERROR", "0で割れません");
        }
        $result = $this->getValue() / $var->getValue();
        return new NumberVariable($resultname, $result);
    }

    public function modulo(Variable $var, string $resultname = "result") {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", "数字を文字列で割ることはできません");
        }
        if ($var->getValue() === 0) {
            return new StringVariable("ERROR", "0で割れません");
        }
        $result = $this->getValue() % $var->getValue();
        return new NumberVariable($resultname, $result);
    }

    public function toStringVariable() {
        $variable = new StringVariable($this->getName(), (string)$this->getValue());
        return $variable;
    }
}