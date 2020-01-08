<?php

namespace aieuo\ip\processes;

use aieuo\ip\IFPlugin;
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
        if (!preg_match("/\s*([0-9]+)\s*:?\s*([0-9]*)\s*:?\s*([0-9]*)\s*:?\s*(.*)\s*/", $id, $ids)) return false;
        $item = Item::get((int)$ids[1], empty($ids[2]) ? 0 : (int)$ids[2], empty($ids[3]) ? 0 : (int)$ids[3]);
        if (!empty($ids[4])) $item->setCustomName($ids[4]);
        return $item;
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $item = explode(":", $default);
        $id = $default;
        $count = "";
        $name = "";
        if (count($item) == 4) {
            $id = $item[0].":".$item[1];
            $count = $item[2];
            $name = $item[3];
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
        $ids_str = $id[0].":".$id[1].":".$data[2].":".$data[3];
        if ($data[1] === "" or $data[2] === "") {
            $status = null;
        } elseif (IFPlugin::getInstance()->getVariableHelper()->containsVariable($ids_str)) {
            $ids = $this->parse($ids_str);
            if ($ids === false) $status = false;
        }
        return ["status" => $status, "contents" => $ids_str, "delete" => $data[4], "cancel" => $data[5]];
    }
}