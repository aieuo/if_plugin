<?php

namespace aieuo\ip\processes;

use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\math\Position;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetSitting extends Process
{
	public $id = self::SET_SITTING;

	public function __construct($player = null, $pos = null)
	{
		parent::__construct($player);
		$this->setValues($pos);
	}

	public function getName()
	{
		return "座らせる";
	}

	public function getDescription()
	{
		return "プレイヤーを§7<pos>§fに座らせる";
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
                Elements::getInput("<pos>\n座標とワールド名を,で区切って入力してください", "例) 1,15,30,world", $defaults),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $default)
	{
	    if(!preg_match("/\s*([0-9]+\.?[0-9]*)\s*,\s*([0-9]+\.?[0-9]*)\s*,\s*([0-9]+\.?[0-9]*)\s*,?\s*(.*)\s*/", $pos, $matches)) return false;
	    if(empty($pos[4])) $pos[4] = "world";
        return new Position((float)$pos[1], (float)$pos[2], (float)$pos[3], Server::getInstance()->getLevelByName($pos[4]));
	}

	public function getPosition() : ?Position
	{
		return $this->getValues();
	}

	public function setPosition(Position $pos)
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
        $pk = new AddEntityPacket();
        $pk->entityRuntimeId = ++Entity::$entityCount;
        $pk->type = 84;
        $pk->position = $pos;
        $pk->links = [new EntityLink($pk->entityRuntimeId, $player->getId(), EntityLink::TYPE_RIDER)];
        $pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_INVISIBLE]
		];
        $player->dataPacket($pk);
	}
}