<?php

namespace aieuo\ip\processes;

use pocketmine\item\Item;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class SetItem extends Process {

    protected $id = self::SET_ITEM;
    protected $name = "@process.setitem.name";
    protected $description = "@process.setitem.description";

    public function getMessage() {
        if ($this->getValues()) return false;
        $item = $this->getItem();
        if (!($item instanceof Item)) return false;
        $index = $this->getIndex();
        return Language::get("process.setitem.detail", [$index, $item->getId(), $item->getDamage(), $item->getName(), $item->getCount()]);
    }

    public function getIndex() {
        return $this->getValues()[0];
    }

    public function getItem() {
        return $this->getValues()[1];
    }

    public function setItems(int $index, Item $item) {
        $this->setValues([$index, $item]);
    }

    public function parse(string $id) {
        if (!preg_match("/\s*([0-9]+)\s*,\s*([0-9]+)\s*:?\s*([0-9]*)\s*:?\s*([0-9]*)\s*:?\s*(\.*)\s*/", $id, $ids)) return false;
        $item = Item::get((int)$ids[2], empty($ids[3]) ? 0 : (int)$ids[3], empty($ids[4]) ? 0 : (int)$ids[4]);
        if (!empty($ids[5])) $item->setCustomName($ids[5]);
        return [(int)$ids[1], $item];
    }

    public function execute() {
        $player = $this->getPlayer();
        if ($this->getValues() === false) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        $item = $this->getItem();
        $index = $this->getIndex();
        $player->getInventory()->setItem($index, $item);
    }


    public function getEditForm(string $default = "", string $mes = "") {
        $items = $this->parse($default);
        $id = $default;
        $count = "";
        $name = "";
        $index = "";
        if ($items === false and $default !== "") {
            $mes .= Language::get("process.item.form.invalid");
        } elseif ($items[1] instanceof Item) {
            $item = $items[1];
            $id = $item->getId().":".$item->getDamage();
            $count = $item->getCount();
            $name = $item->hasCustomName() ? $item->getName() : "";
            if ($count === 0) $mes .= Language::get("process.item.form.zero");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.item.form.id"), Language::get("form.example", ["1:0"]), $id),
                Elements::getInput(Language::get("process.item.form.count"), Language::get("form.example", ["5"]), $count),
                Elements::getInput(Language::get("process.item.form.name"), Language::get("form.example", ["aieuo"]), $name),
                Elements::getInput(Language::get("process.setitem.form.index"), Language::get("form.example", ["0"]), $index),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        $id = explode(":", $datas[1]);
        if (!isset($id[1])) $id[1] = 0;
        $ids_str = $datas[4].",".$id[0].":".$id[1].":".$datas[2].($datas[3] !== "" ? ":".$datas[3] : "");
        if ($datas[1] === "" or $datas[2] === "" or $datas[4] === "") {
            $status = null;
        } else {
            $ids = $this->parse($ids_str);
            if ($ids === false) $status = false;
        }
        return ["status" => $status, "contents" => $ids_str, "delete" => $datas[5], "cancel" => $datas[6]];
    }
}