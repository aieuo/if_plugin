<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class RemoveItem extends Process
{
	public $id = self::REMOVE_ITEM;

	public function __construct($player = null, $item = null)
	{
		parent::__construct($player);
		$this->setValues($item);
	}

	public function getName()
	{
		return "インベントリからアイテムを削除する";
	}

	public function getDescription()
	{
		return "インベントリからidが§7<id>§fのアイテムを§7<count>§f削除する";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$item = $this->parse($defaults);
		$id = $item->getId();
		$count = $item->getCount();
		$name = $item->getName();
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<id>\nアイテムのidを入力してください", "例) 1:0", $id),
                Elements::getInput("<count>\nアイテムの数を入力してください", "例) 5", $count),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $id) : Item
	{
		$ids = explode(":", $id);
		$item = Item::get((int)$ids[0], !empty($ids[1])?(int)$ids[1]:0, !empty($ids[2])?(int)$ids[2]:0);
		if(!empty($ids[3]))
		{
			$item->setCustomName($ids[3]);
		}
		return $item;
	}

	public function getItem() : Item
	{
		return $this->getValues();
	}

	public function setItem(Item $item)
	{
		$this->setValues($item);
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$item = $this->getItem();
        if($item->getCount() > 0){
            $player->getInventory()->removeItem($item);
            return;
        }
        $count = 0;
        foreach ($player->getInventory()->getContents() as $item1) {
            if($item1->getId() == $item->getId() and $item1->getDamage() == $item->getDamage()){
                $count += $item1->getCount();
            }
        }
        $item->setCount($count);
        if($item->getCount() > 0){
            $player->getInventory()->removeItem($item);
        }
	}
}