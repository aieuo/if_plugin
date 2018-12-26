<?php

namespace aieuo\ip\processes;

use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetSleeping extends Process
{
	public $id = self::SET_SLEEPING;

	public function __construct($player = null, $pos = null)
	{
		parent::__construct($player);
		$this->setValues($pos);
	}

	public function getName()
	{
		return "寝かせる";
	}

	public function getDescription()
	{
		return "プレイヤーを§7<pos>§fに寝かせる";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		if($this->parse($defaults) === false)
		{
			$mes = "§c書き方が正しくありません§f";
		}
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<pos>\n座標を,で区切って入力してください", "例) 1,15,30", $defaults),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $content)
	{
        $pos = explode(",", $content);
        if(preg_match("/\s*([0-9]+\.?[0-9]*)\s*,\s*([0-9]+\.?[0-9]*)\s*,\s*([0-9]+\.?[0-9]*)\s*/", $pos, $matches)) return false;
    	$pos = new Vector3((float)$matches[1], (float)$matches[2], (float)$matches[3]);
    	return $pos;
	}

	public function getPosition() : ?Vector3
	{
		return $this->getValues();
	}

	public function setPosition(Vector3 $pos)
	{
		$this->setValues($pos);
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$pos = $this->getPosition();
		if($pos === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
		$player->sleepOn($pos);
	}
}