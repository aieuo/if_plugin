<?php

namespace aieuo\ip\processes;

use pocketmine\level\Position;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class TypePosition extends Process
{
	public function __construct($player = null, $pos = false)
	{
		parent::__construct($player);
		$this->setValues($pos);
	}

	public function getPosition()
	{
		return $this->getValues();
	}

	public function setPosition(string $pos)
	{
		$this->setValues($pos);
	}

	public function parse(string $pos)
	{
	    if(!preg_match("/\s*([0-9]+\.?[0-9]*)\s*,\s*([0-9]+\.?[0-9]*)\s*,\s*([0-9]+\.?[0-9]*)\s*,?\s*(.*)\s*/", $pos, $matches)) return false;
	    if(empty($metches[4])) $metches[4] = "world";
        return new Position((float)$metches[1], (float)$metches[2], (float)$metches[3], Server::getInstance()->getLevelByName($metches[4]));
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
		$pos = $this->parse($default);
		if($pos instanceof Position)
		{
			$position = $pos->x.",".$pos->y.",".$pos->z.",".$pos->level->getFolderName();
		}
		else
		{
			if($default !== "") $mes .= "§c書き方が正しくありません§f";
			$position = $default;
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("\n§7<pos>§f 座標とワールド名を,で区切って入力してください", "例) 1,15,30,world", $position),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}