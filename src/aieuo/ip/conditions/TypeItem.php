<?php

namespace aieuo\ip\conditions;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class TypeItem extends Condition {
    public function getItem() {
        return $this->getValues();
    }

    public function setItem(Item $item) {
        $this->setValues($item);
    }

    public function parse(string $id) {
        if (!preg_match("/\s*([0-9]+)\s*:?\s*([0-9]*)\s*:?\s*([0-9]*)\s*:?\s*(.*)\s*/", $id, $ids)) return false;
        $item = Item::get((int)$ids[1], empty($ids[2]) ? 0 : (int)$ids[2], empty($ids[3]) ? 0 : (int)$ids[3]);
        if (!empty($ids[4])) $item->setCustomName($ids[4]);
        return $item;
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $item = $this->parse($default);
        $id = $default;
        $count = "";
        $name = "";
        if ($item instanceof Item) {
            $id = $item->getId().":".$item->getDamage();
            $count = $item->getCount();
            $name = $item->hasCustomName() ? $item->getName() : "";
            if ($count === 0) $mes .= Language::get("condition.item.form.zero");
        } elseif ($default !== "") {
            $mes .= Language::get("condition.item.form.invalid");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("condition.item.form.id"), Language::get("input.example", ["1:0"]), $id),
                Elements::getInput(Language::get("condition.item.form.count"), Language::get("input.example", ["5"]), $count),
                Elements::getInput(Language::get("condition.item.form.name"), Language::get("input.example", ["aieuo"]), $name),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $data) {
        $status = true;
        $id = explode(":", $data[1]);
        if (!isset($id[1])) $id[1] = 0;
        $ids_str = $id[0].":".$id[1].":".$data[2].":".$data[3];
        if ($data[1] === "" or $data[2] === "") {
            $status = null;
        } else {
            $ids = $this->parse($ids_str);
            if ($ids === false) $status = false;
        }
        return ["status" => $status, "contents" => $ids_str, "delete" => $data[4], "cancel" => $data[5]];
    }
}