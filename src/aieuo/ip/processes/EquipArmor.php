<?php

namespace aieuo\ip\processes;

use pocketmine\item\ItemBlock;
use pocketmine\item\Item;
use pocketmine\item\Armor;
use aieuo\ip\utils\Language;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class EquipArmor extends Process {

    protected $id = self::EQUIP_ARMOR;
    protected $name = "@process.equipArmor.name";
    protected $description = "@process.equipArmor.description";

    private $places = [
        "process.equipArmor.helmet",
        "process.equipArmor.chestplate",
        "process.equipArmor.leggings",
        "process.equipArmor.boots",
    ];

    public function getDetail(): string {
        $place = $this->getPlace();
        $armor = $this->getArmor();
        if (!is_int($place) or !($armor instanceof Item)) return false;
        return Language::get("process.equipArmor.detail", [Language::get($this->places[$place]), $armor->getId(), $armor->getDamage(), $armor->getName()]);
    }

    public function getPlace() {
        return $this->getValues()[0];
    }

    public function getArmor() {
        return $this->getValues()[1];
    }

    public function parse(string $content) {
        $place = explode("[item]", $content)[0];
        $id = explode("[item]", $content)[1] ?? "";
        if (!preg_match("/\s*([0-9]+)\s*:?\s*([0-9]*)\s*/", $id, $ids)) return false;
        $item = Item::get((int)$ids[1], empty($ids[2]) ? 0 : (int)$ids[2]);
        return [(int)$place, $item];
    }

    public function execute() {
        $player = $this->getPlayer();
        $place = $this->getPlace();
        $armor = $this->getArmor();
        if (!($armor instanceof Item)) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        } elseif (!($armor instanceof Armor) and !($armor instanceof ItemBlock)) {
            $player->sendMessage(Language::get("process.equipArmor.notArmor", [$this->getName(), $armor->getName()]));
            return;
        }
        $player->getArmorInventory()->setItem($place, $armor);
    }


    public function getEditForm(string $default = "", string $mes = "") {
        $datas = $this->parse($default);
        $place = 0;
        $armor = explode("[item]", $default)[1] ?? $default;
        if ($armor instanceof Armor or $armor instanceof ItemBlock) {
            $place = $datas[0];
            $armor = $datas->getId().":".$datas->getDamage();
        } elseif ($default !== "" and $armor instanceof Item) {
            $mes .= Language::get("process.equipArmor.notArmor", [$armor->getName()]);
        } elseif ($default !== "") {
            $mes .= Language::get("process.equipArmor.form.invalid");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getDropdown(Language::get("process.equipArmor.form.place"), array_map(function ($item) {
                    return Language::get($item);
                }, $this->places), $place),
                Elements::getInput(Language::get("process.equipArmor.form.id"), Language::get("input.example", ["5"]), $armor),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        $ids_str = $datas[1]."[item]".$datas[2];
        if ($datas[2] === "") {
            $status = null;
        } else {
            $ids = $this->parse($ids_str);
            if ($ids === false) $status = false;
        }
        if ($datas[1] === "" or $datas[2] === "") $status = null;
        return ["status" => $status, "contents" => $ids_str, "delete" => $datas[3], "cancel" => $datas[4]];
    }
}