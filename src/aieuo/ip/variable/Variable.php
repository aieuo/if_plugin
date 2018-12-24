<?php

namespace aieuo\ip\variable;

class Variable {

	const STRING_VARIABLE = 0;
	const INTEGER_VARIABLE = 1;
	const ARRAY_VARIABLE = 2;

	public function __construct($name, $value, $type = VariableHelper::STRING_VARIABLE){
		$this->name = $name;
		$this->value = $value;
		$this->type = $type;
	}

	public function getName(){
		return $this->name;
	}

	public function getValue(){
		return $this->value;
	}

	public function getType(){
		return $this->type;
	}

	public function Addition(Variable $var){
		if(is_numeric($this->getValue()) and is_numeric($var->getValue())){
			$result = (int)$this->getValue() + (int)$var->getValue();
		}else{
			$result = $this->getValue().$var->getValue();
		}
		return new Variable("result", $result);
	}

	public function Subtraction(Variable $var){
		if(is_numeric($this->getValue()) and is_numeric($var->getValue())){
			$result = (int)$this->getValue() - (int)$var->getValue();
		}else{
			$result = str_replace($var->getValue(), "", $this->getValue());
		}
		return new Variable("result", $result);
	}

	public function Multiplication(Variable $var){
		if(is_numeric($this->getValue()) and is_numeric($var->getValue())){
			$result = (int)$this->getValue() * (int)$var->getValue();
		}elseif(is_numeric($this->getValue())){
			$result = str_repeat($var->getValue(), abs((int)$this->getValue()));
		}elseif(is_numeric($var->getValue())){
			$result = str_repeat($this->getValue(), abs((int)$var->getValue()));
		}else{
			$result = "§c掛け算できません";
		}
		return new Variable("result", $result);
	}

	public function Division(Variable $var){
		if(is_numeric($this->getValue()) and is_numeric($var->getValue())){
			if((int)$var->getValue() == 0){
				$result = "§c0で割れません";
			}else{
				$result = (int)$this->getValue() / (int)$var->getValue();
			}
		}else{
			$result = "§c割り算できません";
		}
		return new Variable("result", $result);
	}

	public function Modulo(Variable $var){
		if(is_numeric($this->getValue()) and is_numeric($var->getValue())){
			$result = (int)$this->getValue() % (int)$var->getValue();
		}else{
			$result = "§c数字ではありません";
		}
		return new Variable("result", $result);
	}
}