<?php

namespace aieuo\ip\conditions;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class GameMode extends Condition {

    protected $id = self::GAMEMODE;
    protected $name = "@condition.gamemode.name";
    protected $description = "@condition.gamemode.description";

    private $gamemodes = [
        "condition.gamemode.survival",
        "condition.gamemode.creative",
        "condition.gamemode.adventure",
        "condition.gamemode.spectator"
    ];

    public function getDetail(): string {
        $gamemode = $this->getGamemode();
        if ($gamemode === false) return false;
        return Language::get("condition.gamemode.detail", [Language::get($this->gamemodes[$gamemode])]);
    }

    public function getGamemode() {
        return $this->getValues();
    }

    public function setGamemode(int $gamemode) {
        $this->setValues($gamemode);
    }

    public function parse(string $gamemode) {
        $intGamemode = Server::getInstance()->getGamemodeFromString($gamemode);
        if ($intGamemode === -1) return false;
        return $intGamemode;
    }

    public function check() {
        $player = $this->getPlayer();
        $gamemode = $this->getGamemode();
        if ($gamemode === false) {
            $player->sendMessage(Language::get("condition.gamemode.notfound"));
            return self::ERROR;
        }
        return $player->getGamemode() === $gamemode ? self::MATCHED : self::NOT_MATCHED;
    }


    public function getEditForm(string $default = "", string $mes = "") {
        if ($default === "") {
            $gamemode = 0;
        } elseif (($gamemode = $this->parse($default)) === false) {
            $mes .= Language::get("condition.gamemode.notfound");
            $gamemode = 0;
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getDropdown(
                    Language::get("condition.gamemode.form.gamemode"),
                    array_map(function ($g) {
                        return Language::get($g);
                    }, $this->gamemodes),
                    $gamemode
                ),
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
        } else {
            $gamemode = $this->parse((string)$data[1]);
            if ($gamemode === false) $status = false;
        }
        return ["status" => $status, "contents" => (string)$data[1], "delete" => $data[2], "cancel" => $data[3]];
    }
}