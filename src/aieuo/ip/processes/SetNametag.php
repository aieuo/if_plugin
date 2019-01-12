<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetNametag extends Process
{
	public $id = self::SET_NAMETAG;

	public function __construct($player = null, $name = "")
	{
		parent::__construct($player);
		$this->setValues($name);
	}

	public function getName()
	{
		return "名前を変える";
	}

	public function getDescription()
	{
		return "表示する名前を§7<name>§fに変える";
	}

	public function getChangeName()
	{
		return $this->getValues();
	}

	public function setChangeName(string $name)
	{
		$this->setValues($name);
	}

	public function toString() : string
	{
		return (string)$this->getChangeName();
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$name = $this->getChangeName();
    	$player->setNametag($name);
    	$player->setDisplayName($name);
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<name>§f 変える名前を入力してください", "例) aieuo", $default),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	if($datas[1] === "") return null;
    	return ["contents" => $datas[1], "delete" => $datas[4], "cancel" => $datas[5]];
    }
}