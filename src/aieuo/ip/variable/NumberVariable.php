<?php

namespace aieuo\ip\variable;

use aieuo\ip\utils\Language;

class NumberVariable extends Variable {

    public $type = Variable::NUMBER;

    public function getString(): string {
        return (string)$this->getValue();
    }

    public function addition(Variable $var, string $resultname = "result"): Variable {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", Language::get("variable.number.add.error"));
        }
        $result = $this->getValue() + $var->getValue();
        return new NumberVariable($resultname, $result);
    }

    public function subtraction(Variable $var, string $resultname = "result"): Variable {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", Language::get("variable.number.sub.error"));
        }
        $result = $this->getValue() - $var->getValue();
        return new NumberVariable($resultname, $result);
    }

    public function multiplication(Variable $var, string $resultname = "result"): Variable {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", Language::get("variable.number.mul.error"));
        }
        $result = $this->getValue() * $var->getValue();
        return new NumberVariable($resultname, $result);
    }

    public function division(Variable $var, string $resultname = "result"): Variable {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", Language::get("variable.number.div.error"));
        }
        if ($var->getValue() === 0) {
            return new StringVariable("ERROR", Language::get("variable.number.div.0"));
        }
        $result = $this->getValue() / $var->getValue();
        return new NumberVariable($resultname, $result);
    }

    public function modulo(Variable $var, string $resultname = "result"): Variable {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", Language::get("variable.number.mod.error"));
        }
        if ($var->getValue() === 0) {
            return new StringVariable("ERROR", Language::get("variable.number.div.0"));
        }
        $result = $this->getValue() % $var->getValue();
        return new NumberVariable($resultname, $result);
    }

    public function toStringVariable(): Variable {
        $variable = new StringVariable($this->getName(), (string)$this->getValue());
        return $variable;
    }
}