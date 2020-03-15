<?php

namespace aieuo\ip\processes;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class RemoveItem extends TypeItem {

    protected $id = self::REMOVE_ITEM;
    protected $name = "@process.removeitem.name";
    protected $description = "@process.removeitem.description";

    public function getDetail(): string {
        $item = $this->getItem();
        if (!($item instanceof Item)) return false;
        return Language::get("process.removeitem.detail", [$item->getId(), $item->getDamage(), $item->getName(), $item->getCount()]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $item = $this->getItem();
        if ($item->getCount() > 0) {
            $player->getInventory()->removeItem($item);
            return;
        }
        $count = 0;
        foreach ($player->getInventory()->getContents() as $item1) {
            if ($item1->getId() == $item->getId() and $item1->getDamage() == $item->getDamage()) {
                $count += $item1->getCount();
            }
        }
        $item->setCount($count);
        if ($item->getCount() > 0) {
            $player->getInventory()->removeItem($item);
        }
    }


    public function getEditForm(string $default = "", string $mes = "") {
        $item = $this->parse($default);
        $id = $default;
        $count = "";
        $name = "";
        $lore = "";
        $enchant = "";
        if ($item instanceof Item) {
            $id = $item->getId().":".$item->getDamage();
            $count = $item->getCount();
            $name = $item->hasCustomName() ? $item->getName() : "";
            $lore = implode(";", $item->getLore());
            $enchant = implode(";", array_map(function (EnchantmentInstance $enchant) {
                return $enchant->getId().",".$enchant->getLevel();
            }, $item->getEnchantments()));
            if ($count === 0) $mes .= Language::get("process.removeitem.removeall");
        } elseif ($default !== "") {
            $mes .= Language::get("process.item.form.invalid");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.item.form.id"), Language::get("input.example", ["1:0"]), $id),
                Elements::getInput(Language::get("process.removeitem.form.count"), Language::get("input.example", ["5"]), $count),
                Elements::getInput(Language::get("process.item.form.name"), Language::get("input.example", ["aieuo"]), $name),
                Elements::getInput(Language::get("process.item.form.lore"), Language::get("input.example", ["aiueo;aieuo;aeiuo"]), $lore),
                Elements::getInput(Language::get("process.item.form.enchant"), Language::get("input.example", ["id,level;1,1;5,10"]), $enchant),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        return Form::encodeJson($data);
    }
}