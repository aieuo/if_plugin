<?php

namespace aieuo\ip\processes;

use pocketmine\event\Event;

use aieuo\ip\IFPlugin;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class CooperationRepeat extends Process {

    protected $id = self::COOPERATION_REPEAT;
    protected $name = "@process.cooperationrepeat.name";
    protected $description = "@process.cooperationrepeat.description";

    public function getDetail(): string {
        $name = $this->getCooperationName();
        $count = $this->getCount();
        return Language::get("process.cooperationrepeat.detail", [$name, $count]);
    }

    public function getCooperationName() {
        return $this->getValues()[0];
    }

    public function getCount() {
        return $this->getValues()[1];
    }

    public function setNames(string $name, int $count) {
        $this->setValues([$name, $count]);
    }

    public function parse(string $content) {
        $datas = explode(";", $content);
        if (!isset($datas[1])) return false;
        $count = array_pop($datas);
        $name = implode(";", $datas);
        return [$name, $count];
    }

    public function execute() {
        $player = $this->getPlayer();
        $manager = IFPlugin::getInstance()->getChainManager();
        if (!$manager->exists($this->getCooperationName())) {
            $player->sendMessage(Language::get("process.cooperation.notfound"));
            return;
        }
        $datas = $manager->get($this->getCooperationName());
        $count = $this->getCount();
        for ($i = 0; $i < $count; $i ++) {
            $options = [
                "player" => $player,
                "count" => $i,
            ];
            if ($this->getEvent() instanceof Event) $options["event"] = $this->getEvent();
            $options["replaces"] = $this->replaceDatas;
            $manager->executeIfMatchCondition(
                $player,
                $datas["if"],
                $datas["match"],
                $datas["else"],
                $options
            );
        }
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $manager = IFPlugin::getInstance()->getChainManager();
        $names = $this->parse($default);
        $name = $default;
        $count = "";
        if ($names === false and $default !== "") {
            $mes .= Language::get("form.error");
        } else {
            $name = $names[0];
            $count = $names[1];
        }
        if ($default !== "" and !$manager->exists($name)) $mes .= Language::get("process.cooperation.notfound");
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.cooperation.form.name"), Language::get("input.example", ["aieuo"]), $name),
                Elements::getInput(Language::get("process.cooperationrepeat.form.count"), Language::get("input.example", ["5"]), $count),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $data) {
        $status = true;
        $status = true;
        if ($data[1] === "" or $data[2] === "") {
            $status = null;
        } else {
            $names = $this->parse($data[1].";".$data[2]);
            if ($names === false) $status = false;
        }
        return ["status" => $status, "contents" => $data[1].";".$data[2], "delete" => $data[3], "cancel" => $data[4]];
    }
}