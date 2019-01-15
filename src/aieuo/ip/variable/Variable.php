<?php

namespace aieuo\ip\variable;

class Variable {

	const STRING = 0;
	const NUMBER = 1;
	const ARRAY = 2;

	public static function create($name, $value, $type = self::STRING) {
		if($type === self::STRING) {
			$var = new StringVariable($name, $value);
		} elseif($type === self::NUMBER) {
			$var = new NumberVariable($name, $value);
		}
		return $var;
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
}