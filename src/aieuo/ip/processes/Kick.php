<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use aieuo\ip\task\KickTask;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class Kick extends Process {

    protected $id = self::KICK;
    protected $name = "@process.kick.name";
    protected $description = "@process.kick.description";

    public function getMessage() {
        $reason = $this->getReason();
        return Language::get("process.kick.detail", [$reason]);
    }

    public function getReason() {
        return $this->getValues();
    }

    public function setReason(string $reason) {
        $this->setValues($reason);
    }

    public function execute() {
        $player = $this->getPlayer();
        $reason = $this->getReason();
        ifPlugin::getInstance()->getScheduler()->scheduleDelayedTask(new KickTask($player, $reason), 5);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.kick.form.reason"), Language::get("input.example", ["悪いことをしたから"]), $default),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        if ($datas[1] === "") $status = null;
        return ["status" => $status, "contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}