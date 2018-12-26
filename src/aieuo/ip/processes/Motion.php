<?php

namespace aieuo\ip\processes;

use pockemine\Server;
use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Motion extends Process
{
	public $id = self::MOTION;

	public function __construct($player = null, $pos = null)
	{
		parent::__construct($player);
		$this->setValues($pos);
	}

	public function getName()
	{
		return "動かす";
	}

	public function getDescription()
	{
		return "プレイヤーを§7<x> <y> <z>§rブロック動かす";
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
	    if(!preg_match("/\s*([0-9]+\.?[0-9]*)\s*,\s*([0-9]+\.?[0-9]*)\s*,\s*([0-9]+\.?[0-9]*)\s*/", $pos, $matches)) return false;
        return new Vector3((float)$pos[1], (float)$pos[2], (int)$pos[3]);
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<x>\nx軸方向に動かす値を入力してください", "例) 1", $defaults),
                Elements::getInput("<y>\ny軸方向に動かす値を入力してください", "例) 10", $defaults),
                Elements::getInput("<z>\nz軸方向に動かす値を入力してください", "例) 100", $defaults),
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
        $player->setMotion($pos);
	}
}