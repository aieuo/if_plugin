<?php

namespace aieuo\ip\ifs;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieui\ip\form\Elements;

class ExistsItem extends IFs
{
	public $id = self::EXISTS_ITEM;

	/** @var Item */
	private $item;

	public function __construct($player = null, $item = null)
	{
		parent::__construct($player);
		$this->item = $item;
	}

	public function getName()
	{
		return "インベントリに指定したアイテムが入ってるか";
	}

	public function getDescription()
	{
		return "インベントリにidが§7<id>§fのアイテムが§7<count>§f個以上あるなら";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$item = $this->parse($defaults);
		$id = $defaults;
		$count = "";
		if($item instanceof Item)
		{
			$id = $item->getId().":".$item->getDamage();
			$count = (string)$item->getCount();
		}
		else
		{
			$mes = "§c正しく入力できていません (idは 1:0 のように数字で入力してください)§f";
		}
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

	public function parse(string $id) : ?Item
	{
		if($id === "" or (strpos($id, ":") === false and !is_numeric($id))) return false;
		$ids = explode(":", $id);
		$item = Item::get((int)$ids[0], empty($ids[1])?(int)$ids[1]:0, empty($ids[2])?(int)$ids[2]:1);
		return $item;
	}

	public function getItem() : Item
	{
		return $this->item;
	}

	public function setItem(Item $item)
	{
		$this->item = $item;
	}

	public function check()
	{
		$player = $this->getPlayer();
		$item = $this->getItem();
        if($player->getInventory()->contains($item))
        {
        	return self::MATCHED;
        }
        return self::NOT_MATCHED;
	}
}