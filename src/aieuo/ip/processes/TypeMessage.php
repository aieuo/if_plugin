<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class TypeMessage extends Process
{
	public function __construct($player = null, $message = "")
	{
		parent::__construct($player);
		$this->setValues($message);
	}

	public function getMessage()
	{
		return $this->getValues();
	}

	public function setMessage(string $message)
	{
		$this->setValues($message);
	}

	public function toString() : string
	{
		return (string)$this->getMessage();
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<message>§f 送るメッセージを入力してください", "例) aieuo", $default),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}