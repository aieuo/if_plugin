<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Elements;
use aieuo\ip\form\Form;
use aieuo\ip\utils\Language;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class AddSound extends Process {

    protected $id = self::ADD_SOUND;
    protected $name = "@process.addSound.name";
    protected $description = "@process.addSound.description";

    public function getDetail(): string {
        $sound = $this->getSound();
        return Language::get("process.addSound.detail", [$sound]);
    }

    public function getSound(): ?string {
        $reason = $this->getValues();
        return is_string($reason) ? $reason : null;
    }

    public function execute() {
        $player = $this->getPlayer();
        $sound = $this->getSound();

        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = $player->x;
        $pk->y = $player->y;
        $pk->z = $player->z;
        $pk->volume = 1.0;
        $pk->pitch = 1.0;
        $player->dataPacket($pk);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.addSound.form.sound"), Language::get("input.example", ["random.levelup"]), $default),
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