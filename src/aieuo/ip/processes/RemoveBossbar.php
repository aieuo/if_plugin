<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Bossbar;
use aieuo\ip\utils\Language;

class RemoveBossbar extends Process {

    protected $id = self::REMOVE_BOSSBAR;
    protected $name = "@process.removeBossbar.name";
    protected $description = "@process.removeBossbar.description";

    public function getDetail(): string {
        $id = $this->getBossbarId();
        return Language::get("process.removeBossbar.detail", [$id]);
    }

    public function getBossbarId(): ?string {
        $id = $this->getValues();
        if (!is_string($id)) return null;
        return $id;
    }

    public function parse(string $content) {
        return $content;
    }

    public function execute() {
        $player = $this->getPlayer();
        if ($this->getValues() === false) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        Bossbar::remove($player, $this->getBossbarId());
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $id = $default;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.showBossbar.form.id"), Language::get("input.example", ["aieuo"]), $id),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $data) {
        $status = true;
        if ($data[1] === "") {
            $status = null;
        }
        return ["status" => $status, "contents" => $data[1], "delete" => $data[2], "cancel" => $data[3]];
    }
}