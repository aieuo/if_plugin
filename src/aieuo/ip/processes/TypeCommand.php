<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class TypeCommand extends Process
{
	public function __construct($player = null, $command = "")
	{
		parent::__construct($player);
		$this->setValues($command);
	}

	public function getCommand()
	{
		return $this->getValues();
	}

	public function setCommand(string $command)
	{
		$this->setValues($command);
	}

	public function toString() : string
	{
		return (string)$this->getCommand();
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
		if($default[0] === "/") $mes .= "§e一つ目の/は取ってください§f";
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<command>§f 実行するコマンドを入力してください", "例) help", $default),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}