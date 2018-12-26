<?php

namespace aieuo\ip\processes;

use pockemine\Server;
use pocketmine\level\Position;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Teleport extends Process
{
	public $id = self::TELEPORT;

	public function __construct($player = null, $pos = null)
	{
		parent::__construct($player);
		$this->setValues($pos);
	}

	public function getName()
	{
		"テレポートする";
	}

	public function getDescription()
	{
		"§7<pos>§rにテレポートする";
	}

	public function getPosition()
	{
		return $this->getValues()[0];
	}

	public function setPosition(string $pos)
	{
		$this->setValues($pos);
	}

	public function parse(string $default)
	{
	    if(!preg_match("/\s*([0-9]+\.?[0-9]*)\s*,\s*([0-9]+\.?[0-9]*)\s*,\s*([0-9]+\.?[0-9]*)\s*,?\s*(.*)\s*/", $pos, $matches)) return false;
	    if(empty($pos[4])) $pos[4] = "world";
        return new Position((float)$pos[1], (float)$pos[2], (float)$pos[3], Server::getInstance()->getLevelByName($pos[4]));
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<pos>\n座標を,で区切って入力してください", "例) 0,0,0,world", $defaults),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function excute()
	{
		$player = $this->getPlayer();
		$pos = $this->getPosition();
		if($pos === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
        $player->teleport($pos);
	}
}