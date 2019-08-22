<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use aieuo\ip\task\DelayedCommandTask;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class DelayedCommand extends Process {

    protected $id = self::DELAYED_COMMAND;
    protected $name = "@process.delayedcommand.name";
    protected $description = "@process.delayedcommand.description";

    public function getDetail(): string {
        if ($this->getValues() === false) return false;
        $command = $this->getCommand();
        $time = $this->getTime();
        return Language::get("process.delayedcommand.detail", [$time, $command]);
    }

    public function getTime() {
        return $this->getValues()[1];
    }

    public function getCommand() {
        return $this->getValues()[0];
    }

    public function setCommands(string $command, int $time) {
        $this->setValues($command, $time);
    }

    public function parse(string $commands) {
        if (!preg_match("/([0-9]+),(.+)/", $commands, $matches)) return false;
        return [$matches[2], (int)$matches[1]];
    }

    public function execute() {
        $player = $this->getPlayer();
        if ($this->getValues() === false) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        $time = $this->getTime();
        $command = $this->getCommand();
        ifPlugin::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCommandTask($player, $command), $time*20);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $commands = $this->parse($default);
        $command = $default;
        $time = "";
        if ($commands !== false) {
            $command = $commands[0];
            $time = $commands[1];
        } elseif ($default !== "") {
            $mes .= Language::get("form.error");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.delayedcommand.form.command"), Language::get("input.example", ["help"]), $command),
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
            $value = $this->parse($datas[2].",".$datas[1]);
            if ($value === false) $status = false;
        }
        return ["status" => $status, "contents" => $datas[2].",".$datas[1], "delete" => $datas[3], "cancel" => $datas[4]];
    }
}