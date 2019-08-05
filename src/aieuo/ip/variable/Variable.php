<?php
namespace aieuo\ip\variable;

abstract class Variable {
	const STRING = 0;
	const NUMBER = 1;
	const LIST = 2;
	const MAP = 3;
	/** @var string �ϐ��̖��O */
	protected $name;
	/** @var string �ϐ��̒l */
	protected $value;
	/** @var int �ϐ��̌^ */
	protected $type;
	
	public static function create($name, $value, $type = self::STRING) {
		if($type === self::STRING) {
			$var = new StringVariable($name, $value);
		} elseif($type === self::NUMBER) {
			$var = new NumberVariable($name, $value);
		} elseif($type === self::LIST) {
			if(is_array($value)) {
				$var = new ListVariable($name, $value);
			} else {
                $var = (new StringVariable("string", $value))->division(new StringVariable("delimiter", ", "), $name);
			}
		}
		return $var;
	}

	public function __construct($name, $value) {
		$this->name = $name;
		$this->value = $value;
	}

	public function getName(){
		return $this->name;
	}

    /**
     * @return string|int|array
     */
	public function getValue(){
		return $this->value;
	}

	public function getType(){
		return $this->type;
	}

	/**
	 * �ϐ����m�𑫂�
	 * @param Variable $var
	 * @param string   $name
	 */
    abstract public function addition(Variable $var, string $name = "result");
    
	/**
	 * �ϐ����m������
	 * @param Variable $var
	 * @param string   $name
	 */
    abstract public function subtraction(Variable $var, string $name = "result");
    
	/**
	 * �ϐ����m���|����
	 * @param Variable $var
	 * @param string   $name
	 */
    abstract public function multiplication(Variable $var, string $name = "result");
    
	/**
	 * �ϐ����m������
	 * @param Variable $var
	 * @param string   $name
	 */
    abstract public function division(Variable $var, string $name = "result");
    
	/**
	 * �ϐ����m���������]��
	 * @param Variable $var
	 * @param string   $name
	 */
    abstract public function modulo(Variable $var, string $name = "result");
}