<?php

namespace aieuo\ip\conditions;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class RemoveItem extends TypeItem {

    protected $id = self::REMOVE_ITEM;
    protected $name = "@condition.removeitem.name";
    protected $description = "@condition.removeitem.description";

    public function getDetail(): string {
        $item = $this->getItem();
        if (!($item instanceof Item)) return false;
        return Language::get("condition.removeitem.detail", [$item->getId(), $item->getDamage(), $item->getName(), $item->getCount()]);
    }

    public function check() {
        $player = $this->getPlayer();
        $item = $this->getItem();
        if (!($item instanceof Item)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return self::ERROR;
        }
        if ($item->getCount() === 0) {
            $player->getInventory()->remove($item);
            return self::MATCHED;
        }
        if ($player->getInventory()->contains($item)) {
            $player->getInventory()->removeItem($item);
            return self::MATCHED;
        }
        return self::NOT_MATCHED;
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $item = explode(":", $default);
        $id = $default;
        $count = "";
        $name = "";
        $lore = "";
        $enchant = "";
        if (count($item) >= 3) {
            $id = $item[0].":".$item[1];
            $count = $item[2];
            $name = $item[3] ?? "";
            $lore = $item[4] ?? "";
            $enchant = $item[5] ?? "";
            if ($count === 0) $mes .= Language::get("condition.removeitem.all");
        } elseif ($default !== "") {
            $mes .= Language::get("condition.item.form.invalid");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("condition.item.form.id"), Language::get("input.example", ["1:0"]), $id),
                Elements::getInput(Language::get("condition.removeitem.form.count"), Language::get("input.example", ["5"]), $count),
                Elements::getInput(Language::get("condition.item.form.name"), Language::get("input.example", ["aieuo"]), $name),
                Elements::getInput(Language::get("process.item.form.lore"), Language::get("input.example", ["aiueo;aieuo;aeiuo"]), $lore),
                Elements::getInput(Language::get("process.item.form.enchant"), Language::get("input.example", ["id,level;1,1;5,10"]), $enchant),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        return Form::encodeJson($data);
    }
}