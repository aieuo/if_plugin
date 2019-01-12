<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class RemoveItem extends TypeItem
{
	public $id = self::REMOVE_ITEM;

	public function getName()
	{
		return "インベントリからアイテムを削除する";
	}

	public function getDescription()
	{
		return "インベントリからidが§7<id>§fのアイテムを§7<count>§f削除する";
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$item = $this->getItem();
        if($item->getCount() > 0)
        {
            $player->getInventory()->removeItem($item);
            return;
        }
        $count = 0;
        foreach ($player->getInventory()->getContents() as $item1)
        {
            if($item1->getId() == $item->getId() and $item1->getDamage() == $item->getDamage())
            {
                $count += $item1->getCount();
            }
        }
        $item->setCount($count);
        if($item->getCount() > 0)
        {
            $player->getInventory()->removeItem($item);
        }
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
		$item = $this->parse($default);
		$id = $default;
		$count = "";
		$name = "";
		if($item instanceof Item)
		{
			$id = $item->getId().":".$item->getDamage();
			$count = $item->getCount();
			$name = $item->hasCustomName() ? $item->getName() : "";
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
                Elements::getInput("\n§7<name>§f アイテムに付けたい名前を入力してください(空白ならそのままの名前です)", "例) aieuo", $name),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}