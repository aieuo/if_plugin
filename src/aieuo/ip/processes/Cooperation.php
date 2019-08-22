<?php

namespace aieuo\ip\processes;

use pocketmine\event\Event;

use aieuo\ip\IFPlugin;
use aieuo\ip\utils\Language;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Cooperation extends Process {

    protected $id = self::COOPERATION;
    protected $name = "@process.cooperation.name";
    protected $description = "@process.cooperation.description";

    public function getDetail(): string {
        $name = $this->getCooperationName();
        return Language::get("process.cooperation.detail", [$name]);
    }

    public function getCooperationName(): ?string {
        $name = $this->getValues();
        return is_string($name) ? $name : null;
    }

    public function setCooperationName(string $name) {
        $this->setValues($name);
    }

    public function execute() {
        $player = $this->getPlayer();
        $manager = IFPlugin::getInstance()->getChainManager();
        if (!$manager->exists($this->getCooperationName())) {
            $player->sendMessage(Language::get("process.cooperation.notfount"));
            return;
        }
        $datas = $manager->get($this->getCooperationName());
        $options = [
            "player" => $player,
        ];
        if ($this->getEvent() instanceof Event) $options["event"] = $this->getEvent();
        $manager->executeIfMatchCondition(
            $player,
            $datas["if"],
            $datas["match"],
            $datas["else"],
            $options
        );
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $manager = IFPlugin::getInstance()->getChainManager();
        if ($default !== "" and !$manager->exists($default)) $mes .= Language::get("process.cooperation.notfount");
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.cooperation.form.name"), Language::get("input.example", ["aieuo"]), $default),
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