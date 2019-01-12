<?php

namespace aieuo\ip\conditions;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class RemoveItem extends TypeItem
{
	public $id = self::REMOVE_ITEM;

	public function getName()
	{
		return "指定したアイテムがインベントリにあるなら削除する";
	}

	public function getDescription()
	{
		return "インベントリからidが§7<id>§fのアイテムを§7<count>§f個削除できるなら";
	}

	public function getMessage() {
		$item = $this->getItem();
		if(!($item instanceof Item)) return false;
		return "インベントリから(".$item->getId().":".$item->getDamage().")"."を".$item->getCount()."個削除できるなら";
	}

	public function check()
	{
		$player = $this->getPlayer();
		$item = $this->getItem();
	    if(!($item instanceof Item))
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return self::ERROR;
		}
		if($item->getCount() === 0)
		{
            $count = 0;
            foreach ($player->getInventory()->getContents() as $item1)
            {
                if($item1->getId() == $item->getId() and $item1->getDamage() == $item->getDamage()) $count += $item1->getCount();
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

	public function getEditForm(string $default = "", string $mes = "")
	{
		$item = $this->parse($default);
		$id = $default;
		$count = "";
		if($item instanceof Item)
		{
			$id = $item->getId().":".$item->getDamage();
			$count = $item->getCount();
			if($count === 0) $mes .= "§e指定したアイテムをインベントリからすべて削除します§f";
		}
		elseif($default !== "")
		{
			$mes .= "§c正しく入力できていません (idは0以上の数字で入力してください)§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<id>§f アイテムのidを入力してください", "例) 1:0", $id),
                Elements::getInput("\n§7<count>§f アイテムの数を入力してください(全て消す場合は0を入力するか空白にしてください)", "例) 5", $count),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}