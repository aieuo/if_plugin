<?php

namespace aieuo\ip\processes;

use aieuo\ip\IFPlugin;
use aieuo\ip\task\KickTask;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;
use aieuo\ip\variable\ListVariable;
use pocketmine\item\Item;

class GetInventoryContents extends Process {

    protected $id = self::GET_INVENTORY_CONTENTS;
    protected $name = "@process.getInventory.name";
    protected $description = "@process.getInventory.description";

    public function getDetail(): string {
        $reason = $this->getResultName();
        return Language::get("process.getInventory.detail", [$reason]);
    }

    public function getResultName(): ?string {
        $reason = $this->getValues();
        return is_string($reason) ? $reason : null;
    }

    public function execute() {
        $player = $this->getPlayer();
        $result = $this->getResultName();
        $contents = $player->getInventory()->getContents();

        $variable = new ListVariable($result, array_map(function (Item $item) {
            return $item->getId().":".$item->getDamage();
        }, $contents));
        IFPlugin::getInstance()->getVariableHelper()->add($variable);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.getInventory.form.result"), Language::get("input.example", ["items"]), $default),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        return Form::encodeJson($data);
    }

    public function parseFormData(array $data) {
        $status = true;
        if ($data[1] === "") $status = null;
        return ["status" => $status, "contents" => $data[1], "delete" => $data[2], "cancel" => $data[3]];
    }
}