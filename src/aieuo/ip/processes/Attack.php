<?php

namespace aieuo\ip\processes;

use pocketmine\event\entity\EntityDamageEvent;

use aieuo\ip\utils\Language;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Attack extends Process {

    protected $id = self::ATTACK;
    protected $name = "@process.attack.name";
    protected $description = "@process.attack.description";

    public function getMessage() {
        $damage = $this->getDamage();
        if ($damage === false) return false;
        return Language::get("process.attack.detail", [$damage]);
    }

    public function getDamage() {
        return $this->getValues();
    }

    public function setDamage(float $damage) {
        $this->setValues($damage);
    }

    public function parse(string $content) {
        $damage = (float)$content;
        if ($damage <= 0) return false;
        return $damage;
    }

    public function execute() {
        $player = $this->getPlayer();
        $damage = $this->getDamage();
        if ($damage === false) {
            $player->sendMessage(Language::get("process.attack.error", [$this->getName()]));
            return;
        }
        $event = new EntityDamageEvent($player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, (float)$damage);
        $player->attack($event);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $damage = $this->parse($default);
        if ($damage === false) {
            if ($default !== "") $mes .= Language::get("process.attack.form.error");
            $damage = $default;
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.attack.form.damage"), Language::get("form.example", ["5"]), $damage),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        if ($datas[1] === "") {
            $status = null;
        } else {
            $damage = $this->parse($datas[1]);
            if ($damage === false) $status = false;
        }
        return ["status" => $status, "contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}