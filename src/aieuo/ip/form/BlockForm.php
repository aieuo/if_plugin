<?php

namespace aieuo\ip\form;

use aieuo\ip\Session;
use aieuo\ip\utils\Language;
use aieuo\ip\formAPI\element\Button;
use aieuo\ip\formAPI\ListForm;
use pocketmine\Player;

class BlockForm {
    public function sendSelectActionForm(Player $player) {
        return (new ListForm("@form.block.action.title"))
            ->setContent("@form.selectButton")
            ->setButtons([
                new Button(Language::get("form.action.edit")),
                new Button("@form.action.check"),
                new Button("@form.action.delete"),
                new Button("@form.action.copy"),
                new Button("@form.cancel"),
                new Button("@form.back")
            ])->onReceive(function (Player $player, int $data) {
                $session = Session::getSession($player);
                switch ($data) {
                    case 0:
                        $session->set("action", "edit");
                        $player->sendMessage(Language::get("form.block.action.edit"));
                        break;
                    case 1:
                        $session->set("action", "check");
                        $player->sendMessage(Language::get("form.block.action.check"));
                        break;
                    case 2:
                        $session->set("action", "del");
                        $player->sendMessage(Language::get("form.block.action.delete"));
                        break;
                    case 3:
                        $session->set("action", "copy");
                        $player->sendMessage(Language::get("form.block.action.copy"));
                        break;
                    case 4:
                        $session->setValid(false);
                        $player->sendMessage(Language::get("form.cancelled"));
                        return;
                    case 5:
                        (new Form())->sendSelectIfTypeForm($player);
                        return;
                }
                $session->setValid()->set("if_type", Session::BLOCK);
            })->show($player);
    }
}