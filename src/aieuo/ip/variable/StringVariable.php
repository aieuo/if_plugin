<?php

namespace aieuo\ip\variable;

class StringVariable extends Variable {

    public $type = Variable::STRING;

    public function getString() {
        return $this->getValue();
    }

    public function addition(Variable $var, string $name = "result"): Variable {
        $result = $this->getValue().$var->getValue();
        return new StringVariable($name, $result);
    }

    public function subtraction(Variable $var, string $name = "result"): Variable {
        $result = str_replace((string)$var->getValue(), "", $this->getValue());
        return new StringVariable($name, $result);
    }

    public function multiplication(Variable $var, string $name = "result"): Variable {
        if ($var->getType() !== Variable::NUMBER) {
            return new StringVariable("ERROR", "文字列と文字列を掛けることはできません");
        }
        if ($var->getValue() <= 0) {
            return new StringVariable("ERROR", "掛ける数は1以上にしてください");
        }
        $result = str_repeat($this->getValue(), (int)$var->getValue());
        return new StringVariable($name, $result);
    }

    public function division(Variable $var, string $name = "result"): Variable {
        if ($var->getType() !== Variable::STRING) {
            return new StringVariable("ERROR", "文字列をリストで割ることはできません");
        }
        $result = array_map(function ($value) {
            return trim(rtrim($value));
        }, explode((string)$var->getValue(), (string)$this->getValue()));
        return new ListVariable($name, $result);
    }

    public function modulo(Variable $var, string $name = "result"): Variable {
        return new StringVariable("ERROR", "文字列は割り算できません");
    }
}