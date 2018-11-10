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

	public function Addition($value){
		var_dump($this->getValue(), $value);
		if(is_numeric($this->getValue()) and is_numeric($value)){
			$result = (int)$this->getValue() + (int)$value;
		}else{
			$result = $this->getValue().$value;
		}
		return new Variable("result", $result);
	}

	public function Subtraction($value){
		if(is_numeric($this->getValue()) and is_numeric($value)){
			$result = (int)$this->getValue() - (int)$value;
		}else{
			$result = str_replace($value, "", $value);
		}
		return new Variable("result", $result);
	}

	public function Multiplication($value){
		if(is_numeric($this->getValue()) and is_numeric($value)){
			$result = (int)$this->getValue() * (int)$value;
		}elseif(is_numeric($this->getValue())){
			$result = str_repeat($value, abs((int)$this->getValue()));
		}elseif(is_numeric($value)){
			$result = str_repeat($this->getValue(), abs((int)$value));
		}else{
			$result = "§c掛け算できません";
		}
		return new Variable("result", $result);
	}

	public function Division($value){
		if(is_numeric($this->getValue()) and is_numeric($value)){
			if((int)$value == 0){
				$result = "§c0で割れません";
			}else{
				$result = (int)$this->getValue() / (int)$value;
			}
		}else{
			$result = "§c割り算できません";
		}
		return new Variable("result", $result);
	}

	public function Modulo($value){
		if(is_numeric($this->getValue()) and is_numeric($value)){
			$result = (int)$this->getValue() % (int)$value;
		}else{
			$result = "§c数字ではありません";
		}
		return new Variable("result", $result);
	}
}
