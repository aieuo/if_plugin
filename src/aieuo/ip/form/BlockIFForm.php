<?php

namespace aieuo\ip\form;

use pocketmine\Player;
use aieuo\ip\form\elements\Button;
use aieuo\ip\manager\IFManager;
use aieuo\ip\Session;
use aieuo\ip\utils\Language;

class BlockIFForm {
    public function sendSelectActionForm(Player $player) {
        FormAPI::createListForm("@form.block.action.title")
            ->setContent("@form.selectButton")
            ->addButton(
                new Button("@form.action.edit"),
                new Button("@form.action.delete"),
                new Button("@form.action.copy"),
                new Button("@form.cancel"),
                new Button("@form.back")
            )->onRecive(function (Player $player, ?int $data) {
                if ($data === null) return;
                $session = Session::getSession($player);
                switch ($data) {
                    case 0:
                        $session->set("blockIF_action", "edit");
                        $player->sendMessage(Language::get("form.block.action.edit"));
                        break;
                    case 1:
                        $session->set("blockIF_action", "del");
                        $player->sendMessage(Language::get("form.block.action.delete"));
                        break;
                    case 2:
                        $session->set("blockIF_action", "copy");
                        $player->sendMessage(Language::get("form.block.action.copy"));
                        break;
                    case 3:
                        $session->setValid(false);
                        $player->sendMessage(Language::get("form.cancelled"));
                        return;
                    case 4:
                        (new IFForm)->sendSelectIFTypeForm($player);
                        return;
                }
                $session->setValid()->set("if_type", IFManager::BLOCK);
            })->show($player);
    }
}