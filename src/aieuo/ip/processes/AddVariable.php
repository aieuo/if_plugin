<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use aieuo\ip\variable\Variable;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class AddVariable extends Process
{
	public $id = self::ADD_VARIABLE;

	public function __construct($player = null, $variable = false)
	{
		parent::__construct($player);
		$this->setValues($variable);
	}

	public function getName()
	{
		return "変数を追加する";
	}

	public function getDescription()
	{
		return "§7<name>§fという名前で§7<value>§fという値の変数を追加する";
	}

	public function getMessage() {
		$variable = $this->getVariable();
		if($variable === false) return false;
		return $variable->getName()."という名前で".$variable->getValue()."という値の変数を追加する";
	}

	public function getVariable()
	{
		return $this->getValues();
	}

	public function setVariable(Variable $variable)
	{
		$this->setValues($variable);
	}

	public function parse(string $content)
	{
        $datas = explode(";", $content);
        if(!isset($datas[1]) or $datas[1] === "") return false;
        $helper = ifPlugin::getInstance()->getVariableHelper();
        $value = $helper->changeType($datas[1]);
        return Variable::create($datas[0], $value, $helper->getType($datas[1]));
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$variable = $this->getVariable();
		if($variable === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
        ifPlugin::getInstance()->getVariableHelper()->add($variable);
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
		$var = $this->parse($default);
		$name = $default;
		$value = "";
		if($var instanceof Variable)
		{
			$name = $var->getName();
			$value = $var->getValue();
			if(is_numeric($value) and $var->getType() === Variable::STRING) {
				$value = "(str)".$value;
			} elseif(!is_numeric($value) and $var->getType() === Variable::NUMBER) {
				$value = "(num)".$value;
			}
		}
		elseif($default !== "")
		{
			$mes .= "§c正しく入力できていません§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<name>§f 変数の名前を入力してください", "例) aieuo", $name),
                Elements::getInput("\n§7<value>§f 変数の値を入力してください", "例) 1000", $value),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	$status = true;
    	$var_str = $datas[1].";".$datas[2];
    	if($datas[1] === "" or $datas[2] === "") {
    		$status = null;
    	} else {
	    	$var = $this->parse($var_str);
	    	if($var === false) $status = false;
	    }
    	return ["status" => $status, "contents" => $var_str, "delete" => $datas[3], "cancel" => $datas[4]];
    }
}