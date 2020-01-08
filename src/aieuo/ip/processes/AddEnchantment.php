<?php

namespace aieuo\ip\processes;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

use aieuo\ip\utils\Language;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class AddEnchantment extends Process {

    protected $id = self::ADD_ENCHANTMENT;
    protected $name = "@process.addenchant.name";
    protected $description = "@process.addenchant.description";

    public function getDetail(): string {
        $enchant = $this->getEnchantment();
        if (!($enchant instanceof EnchantmentInstance)) return false;
        return Language::get("process.addenchant.detail", [$enchant->getId(), $enchant->getLevel()]);
    }

    public function getEnchantment() {
        return $this->getValues();
    }

    public function setEnchantment(EnchantmentInstance $enchant) {
        $this->setValues($enchant);
    }

    public function parse(string $content) {
        $args = explode(",", $content);
        if (!isset($args[1]) or (int)$args[1] <= 0) $args[1] = 1;
        if (is_numeric($args[0])) {
            $enchantment = Enchantment::getEnchantment((int)$args[0]);
        } else {
            $enchantment = Enchantment::getEnchantmentByName($args[0]);
        }
        if (!($enchantment instanceof Enchantment)) return null;
        return new EnchantmentInstance($enchantment, (int)$args[1]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $enchant = $this->getEnchantment();
        if (!($enchant instanceof EnchantmentInstance)) {
            if ($enchant === false) $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            if ($enchant === null) $player->sendMessage(Language::get("process.addenchant.notfound"));
            return;
        }
        $item = $player->getInventory()->getItemInHand();
        $item->addEnchantment($enchant);
        $player->getInventory()->setItemInHand($item);
    }


    public function getEditForm(string $default = "", string $mes = "") {
        $enchant = $this->parse($default);
        $id = $default;
        $power = "";
        if ($enchant instanceof EnchantmentInstance) {
            $id = $enchant->getId();
            $power = $enchant->getLevel();
        } elseif ($default !== "") {
            if ($enchant === false) $mes .= Language::get("form.error");
            if ($enchant === null) $mes .= Language::get("process.addenchant.notfound");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.addenchant.form.id"), Language::get("input.example", ["1"]), $id),
                Elements::getInput(Language::get("process.addenchant.form.power"), Language::get("input.example", ["5"]), $power),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $data) {
        $status = true;
        $enchant_str = $data[1].",".$data[2];
        if ($data[1] === "" or $data[2] === "") $status = null;
        return ["status" => $status, "contents" => $enchant_str, "delete" => $data[3], "cancel" => $data[4]];
    }
}