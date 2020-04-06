<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Elements;
use aieuo\ip\form\Form;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

use aieuo\ip\utils\Language;

class ChangeItemData extends Process {

    protected $id = self::CHANGE_ITEM_DATA;
    protected $name = "@process.changeItemData.name";
    protected $description = "@process.changeItemData.description";

    public function getDetail(): string {
        return Language::get("process.changeItemData.detail", $this->getValues());
    }

    public function getDamage(): ?string {
        return $this->getValues()[0] ?? null;
    }

    public function getCount(): ?string {
        return $this->getValues()[1] ?? null;
    }

    public function getCustomItemName(): ?string {
        return $this->getValues()[2] ?? null;
    }

    public function getLore(): ?string {
        return $this->getValues()[3] ?? null;
    }

    public function getEnchant(): ?string {
        return $this->getValues()[4] ?? null;
    }

    public function parse(string $content) {
        $ids = explode("[:]", $content);
        $damage = $ids[0] === "" ? null : $ids[0];
        $count = (!isset($ids[1]) or $ids[1] === "") ? null : $ids[1];
        $name = (!isset($ids[2]) or $ids[2] === "") ? null : $ids[2];
        $lore = (!isset($ids[3]) or $ids[3] === "") ? null : $ids[3];
        $enchant = (!isset($ids[4]) or $ids[4] === "") ? null : $ids[4];
        return [$damage, $count, $name, $lore, $enchant];
    }

    public function execute() {
        $player = $this->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        $damage = $this->getDamage();
        $count = $this->getCount();
        $name = $this->getCustomItemName();
        $lore = $this->getLore();
        $enchant = $this->getEnchant();

        if ($damage !== null) $item->setDamage((int)$damage);
        if ($count !== null) $item->setCount((int)$count);
        if ($name !== null) $item->setCustomName($name);
        if ($lore !== null) $item->setLore(explode(";", $lore));
        if ($enchant !== null) {
            $item->removeEnchantments();
            $enchants1 = explode(";", $enchant);
            foreach ($enchants1 as $enchant1) {
                $ench = explode(",", trim($enchant1));
                $enchant = is_numeric(trim($ench[0])) ? Enchantment::getEnchantment(trim($ench[0])) : Enchantment::getEnchantmentByName(trim($ench[0]));

                if (!($enchant instanceof Enchantment)) continue;

                $level = (int)trim($ench[1] ?? "1");
                $item->addEnchantment(new EnchantmentInstance($enchant, $level));
            }
        }

        $player->getInventory()->setItemInHand($item);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $item = explode("[:]", $default);
        $damage = $default;
        $count = "";
        $name = "";
        $lore = "";
        $enchant = "";
        if (isset($item[4])) {
            $damage = $item[0];
            $count = $item[1];
            $name = $item[2];
            $lore = $item[3];
            $enchant = $item[4];
        } elseif ($default !== "") {
            $mes .= Language::get("process.item.form.invalid");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.changeItemData.form.damage"), Language::get("input.example", ["1:0"]), $damage),
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
        $ids_str = $data[1]."[:]".$data[2]."[:]".$data[3]."[:]".$data[4]."[:]".$data[5];
        return ["status" => $status, "contents" => $ids_str, "delete" => $data[6], "cancel" => $data[7]];
    }
}