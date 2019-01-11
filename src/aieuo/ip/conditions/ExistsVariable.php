<?php

namespace aieuo\ip\conditions;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class ExistsVariable extends Condition
{
	public $id = self::EXISTS_VARIABLE;

	public function __construct($player = null, $variable = "")
	{
		parent::__construct($player);
		$this->setValues($variable);
	}

	public function getName()
	{
		return "変数が存在するか";
	}

	public function getDescription()
	{
		return "変数§7<variable>§fが存在するか";
	}

	public function getVariableName()
	{
		return $this->getValues();
	}

	public function setVariableName(string $variable)
	{
		$this->setValues($variable);
	}

	public function toString() : string
	{
		return (string)$this->getVariableName();
	}

	public function check()
	{
		if(ifPlugin::getInstance()->getVariableHelper()->exists($this->getVariableName())) return self::MATCHED;
		return self::NOT_MATCHED;
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<variable>§f 変数の名前を入力してください", "例) aieuo", $default),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}