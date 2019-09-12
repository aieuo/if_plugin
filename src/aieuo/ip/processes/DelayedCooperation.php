<?php

namespace aieuo\ip\processes;

use aieuo\ip\IFPlugin;
use aieuo\ip\utils\Language;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\task\DelayedCooperationTask;

class DelayedCooperation extends Process {

    protected $id = self::DELAYED_COOPERATION;
    protected $name = "@process.delayedCooperation.name";
    protected $description = "@process.delayedCooperation.description";

    public function getDetail(): string {
        $name = $this->getCooperationName();
        $time = $this->getTime();
        return Language::get("process.delayedCooperation.detail", [$name, $time]);
    }

    public function getCooperationName(): ?string {
        $name = $this->getValues()[1] ?? null;
        return is_string($name) ? $name : null;
    }

    public function getTime(): ?float {
        $time = $this->getValues()[0] ?? null;
        return is_float($time) ? $time : null;
    }

    public function parse(string $content) {
        $names = explode("[name]", $content);
        if (!isset($names[1])) return false;
        if (!preg_match("/([0-9]+.?[0-9]*)/", $names[0], $matches)) return false;
        return [(float)$matches[1], $names[1]];
    }

    public function execute() {
        $player = $this->getPlayer();
        $time = $this->getTime();
        $name = $this->getCooperationName();
        if ($time === null or $name === null) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        IFPlugin::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCooperationTask($player, $name, $this->getEvent(), $this->replaceDatas), $time*20);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $manager = IFPlugin::getInstance()->getChainManager();
        $names = $this->parse($default);
        $name = explode("[name]", $default)[1] ?? $default;
        $time = explode("[name]", $default)[0];
        if ($default !== "" and $names === false) {
            $mes .= Language::get("form.error");
        } elseif ($default !== "" and !$manager->exists($name)) {
            $mes .= Language::get("process.cooperation.notfount");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.cooperation.form.name"), Language::get("input.example", ["aieuo"]), $name),
                Elements::getInput(Language::get("process.delayedcommand.form.time"), Language::get("input.example", ["10"]), $time),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        if ($datas[1] === "" or $datas[2] === "") {
            $status = null;
        } else {
            $value = $this->parse($datas[2]."[name]".$datas[1]);
            if ($value === false) $status = false;
        }
        return ["status" => $status, "contents" => $datas[2]."[name]".$datas[1], "delete" => $datas[3], "cancel" => $datas[4]];
    }
}