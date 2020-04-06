<?php

namespace aieuo\ip\processes;

use aieuo\ip\IFPlugin;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class TypeItem extends Process {

    public function getItem(): ?Item {
        $item = $this->getValues();
        if (!($item instanceof Item)) return null;
        return $item;
    }

    public function setItem(Item $item) {
        $this->setValues($item);
    }

    public function parse(string $id) {
        $ids = explode(":", $id);
        $item = Item::get((int)$ids[0], empty($ids[1]) ? 0 : (int)$ids[1]);
        if (isset($ids[2])) $item->setCount((int)$ids[2]);
        if (!empty($ids[3])) $item->setCustomName($ids[3]);
        if (!empty($ids[4])) $item->setLore(explode(";", $ids[4]));
        if (!empty($ids[5])) {
            $enchants1 = explode(";", $ids[5]);
            foreach ($enchants1 as $enchant1) {
                $enchants = explode(",", trim($enchant1));
                if (is_numeric(trim($enchants[0]))){
                    $enchant = Enchantment::getEnchantment(trim($enchants[0]));
                } else {
                    $enchant = Enchantment::getEnchantmentByName(trim($enchants[0]));
                }

                if(!($enchant instanceof Enchantment)) continue;

                $level = (int)trim($enchants[1] ?? "1");
                $item->addEnchantment(new EnchantmentInstance($enchant, $level));
            }
        }
        return $item;
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
            if ($count === 0) $mes .= Language::get("process.item.form.zero");
        } elseif ($default !== "") {
            $mes .= Language::get("process.item.form.invalid");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.item.form.id"), Language::get("input.example", ["1:0"]), $id),
                Elements::getInput(Language::get("process.item.form.count"), Language::get("input.example", ["5"]), $count),
                Elements::getInput(Language::get("process.item.form.name"), Language::get("input.example", ["aieuo"]), $name),
                Elements::getInput(Language::get("process.item.form.lore"), Language::get("input.example", ["aiueo;aieuo;aeiuo"]), $lore),
                Elements::getInput(Language::get("process.item.form.enchant"), Language::get("input.example", ["id,level;1,1;5,10"]), $enchant),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        return Form::encodeJson($data);
    }

    public function parseFormData(array $data) {
        $status = true;
        $id = explode(":", $data[1]);
        if (!isset($id[1])) $id[1] = 0;
        $ids_str = $id[0].":".$id[1].":".$data[2].":".$data[3].":".$data[4].":".$data[5];
        if ($data[1] === "" or $data[2] === "") {
            $status = null;
        } elseif (!IFPlugin::getInstance()->getVariableHelper()->containsVariable($ids_str)) {
            $ids = $this->parse($ids_str);
            if ($ids === false) $status = false;
        }
        return ["status" => $status, "contents" => $ids_str, "delete" => $data[6], "cancel" => $data[7]];
    }
}