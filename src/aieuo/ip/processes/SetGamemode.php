<?php

namespace aieuo\ip\processes;

use pocketmine\Server;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Language;

class SetGamemode extends Process {

    protected $id = self::SET_GAMEMODE;
    protected $name = "@process.gamemode.name";
    protected $description = "@process.gamemode.description";

    private $gamemodes = [
        "process.gamemode.survival",
        "process.gamemode.creative",
        "process.gamemode.adventure",
        "process.gamemode.spectator"
    ];

    public function getGamemode(): ?int {
        $gamemode = $this->getValues();
        if (!(is_int($gamemode) and $gamemode >= 0 and $gamemode <= 3)) return null;
        return $gamemode;
    }

    public function setGamemode(int $gamemode) {
        $this->setValues($gamemode);
    }

    public function parse(string $content) {
        $gamemode = Server::getInstance()->getGamemodeFromString($content);
        if ($gamemode === -1) return false;
        return $gamemode;
    }

    public function getDetail(): string {
        $gamemode = $this->getGamemode();
        if ($gamemode === false) return false;
        return Language::get("process.gamemode.detail", [Language::get($this->gamemodes[$gamemode])]);
    }

    public function execute() {
        $player = $this->getPlayer();
        $gamemode = $this->getGamemode();
        if ($gamemode === false) {
            $player->sendMessage(Language::get("process.gamemode.notfound"));
            return;
        }
        $player->setGamemode($gamemode);
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $gamemode = $this->parse($default);
        if ($gamemode === false) {
            if ($default !== "") $mes .= Language::get("process.gamemode.notfound");
            $gamemode = 0;
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getDropdown(
                    Language::get("process.gamemode.form.gamemode"),
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
            $gamemode = $this->parse($data[1]);
            if ($gamemode === false) $status = false;
        }
        return ["status" => $status, "contents" => $data[1], "delete" => $data[2], "cancel" => $data[3]];
    }
}