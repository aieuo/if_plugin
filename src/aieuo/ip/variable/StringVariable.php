<?php

namespace aieuo\ip\variable;

class StringVariable extends Variable{

	public $type = Variable::STRING;

    public function addition(Variable $var, string $name = "result") {
		$result = $this->getValue().$var->getValue();
		return new StringVariable($name, $result);
	}

    public function subtraction(Variable $var, string $name = "result") {
		$result = str_replace((string)$var->getValue(), "", $this->getValue());
		return new StringVariable($name, $result);
	}

    public function multiplication(Variable $var, string $name = "result") {
		if($var->getType() !== Variable::NUMBER) {
			return new StringVariable("ERROR", "文字列と文字列を掛けることはできません");
		}
		if($var->getValue() <= 0) {
			return new StringVariable("ERROR", "掛ける数は1以上にしてください");
		}
		$result = str_repeat($this->getValue(), (int)$var->getValue());
		return new StringVariable($name, $result);
	}

    public function division(Variable $var, string $name = "result") {
		if($var->getType() !== Variable::STRING and $var->getType() !== Variable::NUMBER) {
			return new StringVariable("ERROR", "文字列を文字列以外で割ることはできません");
		}
		$result = explode($var->getValue(), (string)$this->getValue());
		return new ListVariable($name, $result);
	}

    public function modulo(Variable $var, string $name = "result") {
		return new StringVariable("ERROR", "文字列は割り算できません");
	}
}