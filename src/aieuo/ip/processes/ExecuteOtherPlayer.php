<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\IFPlugin;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class ExecuteOtherPlayer extends Process {

    protected $id = self::EXECUTE_OTHER_PLAYER;
    protected $name = "@process.executeotherplayer.name";
    protected $description = "@process.executeotherplayer.description";

    public function getDetail(): string {
        $cname = $this->getCooperationName();
        $playerName = $this->getPlayerNames();
        return Language::get("process.executeotherplayer.detail", [$cname, $playerName]);
    }

    public function getCooperationName() {
        return $this->getValues()[0];
    }

    public function getPlayerNames() {
        return $this->getValues()[1];
    }

    public function setNames(string $name, string $playerName) {
        $this->setValues([$name, $playerName]);
    }

    public function parse(string $content) {
        $data = explode(";", $content);
        if (!isset($data[1])) return false;
        $playerName = array_pop($data);
        $cname = implode(";", $data);
        return [$cname, $playerName];
    }

    public function execute() {
        $player = $this->getPlayer();
        $manager = IFPlugin::getInstance()->getChainManager();
        if (!$manager->exists($this->getCooperationName())) {
            $player->sendMessage(Language::get("process.cooperation.notFound"));
            return;
        }
        $playerNames = explode(",", $this->getPlayerNames());
        foreach ($playerNames as $name) {
            $name = trim($name);
            $target = Server::getInstance()->getPlayer($name);
            if ($target === null) {
                $player->sendMessage(Language::get("process.executeotherplayer.offline", [$name]));
                return;
            }
            $data = $manager->get($this->getCooperationName());
            $manager->executeIfMatchCondition(
                $target,
                $data["if"],
                $data["match"],
                $data["else"],
                [
                    "player" => $target,
                    "origin" => $player
                ]
            );
        }
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $manager = IFPlugin::getInstance()->getChainManager();
        $names = $this->parse($default);
        $cname = $default;
        $playerName = "";
        if ($names === false and $default !== "") {
            $mes .= Language::get("form.error");
        } else {
            $cname = $names[0];
            $playerName = $names[1];
        }
        if ($default !== "" and !$manager->exists($cname)) $mes .= Language::get("process.cooperation.notFound");
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.executeotherplayer.form.name"), Language::get("input.example", ["aieuo"]), $cname),
                Elements::getInput(Language::get("process.executeotherplayer.form.player"), Language::get("input.example", ["aiueo421, aiueo422, aiueo423"]), $playerName),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        return Form::encodeJson($data);
    }

    public function parseFormData(array $data) {
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