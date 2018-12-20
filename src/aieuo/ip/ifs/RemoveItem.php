<?php

namespace aieuo\ip\ifs;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieui\ip\form\Elements;

class RemoveItem extends IFs
{
	public $id = self::REMOVE_ITEM;

	/** @var Item */
	private $item;

	public function __construct($player = null, $item = null)
	{
		parent::__construct($player);

		$this->item = $item;
	}

	public function getName()
	{
		return "指定したアイテムがインベントリにあるなら削除する";
	}

	public function getDescription()
	{
		return "インベントリからidが§7<id>§fのアイテムを§7<count>§f個削除できるなら";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$item = $this->parse($defaults);
		$id = $defaults;
		$count = "";
		if($item instanceof Item and $item->getCount())
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
                Elements::getInput("<count>\nアイテムの数を入力してください(全て消す場合は0を入力するか空白にしてください)", "例) 5", $count),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $id) : Item
	{
		if($id === "" or (strpos($id, ":") === false and !is_numeric($id))) return false;
		$ids = explode(":", $id);
		$item = Item::get((int)$ids[0], empty($ids[1])?(int)$ids[1]:0, empty($ids[2])?(int)$ids[2]:0);
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
		if($item->getCount() == 0)
		{
            $count = 0;
            foreach ($player->getInventory()->getContents() as $item1)
            {
                if(
                	$item1->getId() == $item->getId()
                	and $item1->getDamage() == $item->getId()
                )
                {
                    $count += $item1->getCount();
                }
            }
            if($count == 0) return self::NOT_MATCHED;
            $item->setCount($count);
		}
        if($player->getInventory()->contains($item))
        {
            $player->getInventory()->removeItem($item);
            return self::MATCHED;
        }
        return self::NOT_MATCHED;
	}
}