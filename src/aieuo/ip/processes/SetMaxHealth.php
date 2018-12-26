<?php

namespace aieuo\ip\processes;

use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetMaxHealth extends Process
{
	public $id = self::SET_MAXHEALTH;

	public function __construct($player = null, $health = null)
	{
		parent::__construct($player);
		$this->setValues($health);
	}

	public function getName()
	{
		return "最大体力を変更する";
	}

	public function getDescription()
	{
		return "プレイヤーの最大体力を§7<health>§fにする";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$health = $this->parse($defaults);
		if($health === false)
		{
			$mes = "§c体力は1以上にしてください§f";
			$health = $defaults;
		}
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<health>\n最大体力を入力してください", "例) 40", $health),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $content)
	{
        $health = (int)$content;
        if($health <= 0) return false;
    	return $health;
	}

	public function getHealth() : ?int
	{
		return $this->getValues();
	}

	public function setHealth(int $health)
	{
		$this->setValues($health);
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$health = $this->getHealth();
		if($health === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 最大体力は1以上にしてください");
			return;
		}
		$player->setMaxHealth($health);
	}
}