<?php

namespace aieuo\ip\processes;

use pocketmine\math\Vector3;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetHealth extends Process
{
	public $id = self::SET_HEALTH;

	public function __construct($player = null, $health = 0)
	{
		parent::__construct($player);
		$this->setValues($health);
	}

	public function getName()
	{
		return "体力を変更する";
	}

	public function getDescription()
	{
		return "プレイヤーの体力を§7<health>§fにする";
	}

	public function getHealth()
	{
		return $this->getValues();
	}

	public function setHealth(int $health)
	{
		$this->setValues($health);
	}

	public function parse(string $content)
	{
        $health = (int)$content;
        if($health <= 0) return false;
    	return $health;
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$health = $this->gethealth();
		if($health === false)
		{
			$player->sendMessage("§c[".$this->getName()."] 体力は1以上にしてください");
			return;
		}
		$player->setHealth($health);
	}

	public function getEditForm(string $default = "", string $mes = "")
	{
		$health = $this->parse($default);
		if($health === false)
		{
			$mes = "§c体力は1以上にしてください§f";
			$health = $default;
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<health>§f 体力を入力してください", "例) 10", $health),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}