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
        $pname = $this->getPlayerName();
        return Language::get("process.executeotherplayer.detail", [$cname, $pname]);
    }

    public function getCooperationName() {
        return $this->getValues()[0];
    }

    public function getPlayerName() {
        return $this->getValues()[1];
    }

    public function setNames(string $name, string $playerName) {
        $this->setValues([$name, $playerName]);
    }

    public function parse(string $content) {
        $datas = explode(";", $content);
        if (!isset($datas[1])) return false;
        $pname = array_pop($datas);
        $cname = implode(";", $datas);
        return [$cname, $pname];
    }

    public function execute() {
        $player = $this->getPlayer();
        $manager = ifPlugin::getInstance()->getChainManager();
        if (!$manager->isAdded($this->getCooperationName())) {
            $player->sendMessage(Language::get("process.cooperation.notfount"));
            return;
        }
        $playerName = $this->getPlayerName();
        $target = Server::getInstance()->getPlayer($playerName);
        if ($target === null) {
            $player->sendMessage(Language::get("process.executeotherplayer.offline", [$playerName]));
            return;
        }
        $datas = $manager->get($this->getCooperationName());
        $manager->executeIfMatchCondition(
            $target,
            $datas["if"],
            $datas["match"],
            $datas["else"],
            [
                "player" => $target,
                "origin" => $player
            ]
        );
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $manager = IFPlugin::getInstance()->getChainManager();
        $names = $this->parse($default);
        $cname = $default;
        $pname = "";
        if ($names === false and $default !== "") {
            $mes .= Language::get("form.error");
        } else {
            $cname = $names[0];
            $pname = $names[1];
        }
        if ($default !== "" and !$manager->isAdded($cname)) $mes .= Language::get("process.cooperation.notfount");
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.executeotherplayer.form.name"), Language::get("input.example", ["aieuo"]), $cname),
                Elements::getInput(Language::get("process.executeotherplayer.form.player"), Language::get("input.example", ["aiueo421"]), $pname),
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
            $names = $this->parse($datas[1].";".$datas[2]);
            if ($names === false) $status = false;
        }
        return ["status" => $status, "contents" => $datas[1].";".$datas[2], "delete" => $datas[3], "cancel" => $datas[4]];
    }
}