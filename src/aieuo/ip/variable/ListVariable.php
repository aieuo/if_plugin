<?php

namespace aieuo\ip\variable;

class ListVariable extends Variable {

	public $type = Variable::LIST;

	public function __construct($name, $value) {
		$this->name = $name;
		$this->value = $value;
	}

	public function Addition(Variable $var, string $name = "result") {
		if($var->getType() !== Variable::LIST) {
			return new StringVariable("ERROR", "リストにリスト以外を足すことはできません");
		}
		$result = array_merge($this->getValue(), $var->getValue());
		return new ListVariable($name, $result);
	}

	public function Subtraction(Variable $var, string $name = "result") {
		if($var->getType() !== Variable::LIST) {
			return new StringVariable("ERROR", "リストからリスト以外を引くことはできません");
		}
		$result = array_diff($this->getValue(), $var->getValue());
		$result = array_values($result);
		return new ListVariable($name, $result);
	}

	public function Multiplication(Variable $var, string $name = "result") {
		return new StringVariable("ERROR", "リストは掛け算できません");
		// TODO    convert StringVariable
	}

	public function Division(Variable $var, string $name = "result") {
		return new StringVariable("ERROR", "リストは割り算できません");
	}

	public function Modulo(Variable $var, string $name = "result") {
		return new StringVariable("ERROR", "リストは割り算できません");
	}

	public function getValueFromIndex($index) {
		if(!isset($this->value[$index])) return null;
		return $this->value[$index];
	}

	public function toStringVariable($glue = ", ") {
		$variable = new StringVariable($this->getName(), implode($glue, $this->getValue()));
		return $variable;
	}
}