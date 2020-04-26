<?php

namespace aieuo\ip\form;

use aieuo\ip\formAPI\CustomForm;
use aieuo\ip\formAPI\element\Button;
use aieuo\ip\formAPI\element\Input;
use aieuo\ip\formAPI\element\Toggle;
use aieuo\ip\formAPI\ListForm;
use aieuo\ip\Session;
use aieuo\ip\IFPlugin;
use aieuo\ip\IFAPI;
use aieuo\ip\utils\Language;
use pocketmine\Player;

class ChainIfForm {
    public function sendSelectActionForm(Player $player) {
        (new ListForm("@form.chain.selectAction.title"))
            ->setContent("@form.selectButton")
            ->setButtons([
                new Button("@form.action.add"),
                new Button("@form.action.edit"),
                new Button("@form.action.delete"),
                new Button("@form.chain.list"),
                new Button("@form.cancel"),
                new Button("@form.back")
            ])->onReceive(function (Player $player, int $data) {
                $session = Session::getSession($player);
                switch ($data) {
                    case 0:
                        $session->set("action", "add");
                        $this->sendAddChainIfForm($player);
                        break;
                    case 1:
                        $session->set("action", "edit");
                        $this->sendEditChainIfForm($player);
                        break;
                    case 2:
                        $session->set("action", "del");
                        $this->sendEditChainIfForm($player);
                        break;
                    case 3:
                        $this->sendChainIfListForm($player);
                        break;
                    case 4:
                        $session->setValid(false);
                        $player->sendMessage(Language::get("form.cancelled"));
                        return;
                    case 5:
                        (new Form())->sendSelectIfTypeForm($player);
                        return;
                }
                $session->setValid()->set("if_type", Session::CHAIN);
            })->show($player);
    }

    public function sendAddChainIfForm(Player $player, array $default = [], array $errors = []) {
        (new CustomForm("@form.chain.addChain.title"))
            ->setContents([
                new Input("@form.chain.addChain.content0", "", $default[0] ?? ""),
                new Toggle("@form.cancel"),
            ])->onReceive(function (Player $player, array $data) {
                $session = Session::getSession($player);
                if ($data[1]) {
                    $this->sendSelectActionForm($player);
                    return;
                }
                if ($data[0] === "") {
                    $this->sendAddChainIfForm($player, $data, [["@form.insufficient", 0]]);
                    return;
                }
                $manager = IFPlugin::getInstance()->getChainManager();
                if ($manager->exists($data[0])) {
                    $this->sendAddChainIfForm($player, $data, [["@form.chain.alreadyExists", 0]]);
                    return;
                }
                $session->set("if_key", $data[0]);
                $ifData = $manager->repairIF([]);
                $manager->set($data[0], $ifData);
                (new Form)->sendEditIfForm($player, $ifData);
            })->onClose(function (Player $player) {
                Session::getSession($player)->setValid(false, false);
            })->addErrors($errors)->show($player);
    }

    public function sendEditChainIfForm(Player $player, array $default = [], array $errors = []) {
        (new CustomForm("@form.chain.editChain.title"))
            ->setContents([
                new Input("@form.chain.editChain.content0", "", $default[0] ?? ""),
                new Toggle("@form.cancel"),
            ])->onReceive(function (Player $player, array $data) {
                $session = Session::getSession($player);
                if ($data[1]) {
                    $this->sendSelectActionForm($player);
                    return;
                }
                if ($data[0] === "") {
                    $this->sendEditChainIfForm($player, [["@form.insufficient", 0]]);
                    $player->sendMessage(Language::get("form.insufficient"));
                    return;
                }
                $manager = IFPlugin::getInstance()->getChainManager();
                if (!$manager->exists($data[0])) {
                    $this->sendEditChainIfForm($player, [["@form.chain.notExists", 0]]);
                    return;
                }
                $session->set("if_key", $data[0]);
                $action = $session->get("action");
                if ($action === "edit") {
                    $ifData = $manager->repairIF($manager->get($data[0]));
                    (new Form)->sendEditIfForm($player, $ifData);
                } elseif ($action === "del") {
                    (new Form())->confirmDelete($player, [new Form(), "onDeleteIf"]);
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->setValid(false, false);
            })->addErrors($errors)->show($player);
    }

    public function sendChainIfListForm(Player $player) {
        $data = IFPlugin::getInstance()->getChainManager()->getAll();
        $buttons = [new Button("@form.back")];
        foreach ($data as $name => $ifData) {
            $buttons[] = new Button($name);
        }
        (new ListForm("@form.chain.list.title"))
            ->setContent("@form.selectButton")
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data) {
                $session = Session::getSession($player);
                if ($data === 0) {
                    $this->sendSelectActionForm($player);
                    return;
                }
                $manager = IFPlugin::getInstance()->getChainManager();
                $ifs = array_slice($manager->getAll(), $data-1, 1, true);
                $key = key($ifs);
                $ifData = current($ifs);
                $session->set("if_key", $key);
                (new Form)->sendEditIfForm($player, $ifData);
            })->onClose(function (Player $player) {
                Session::getSession($player)->setValid(false, false);
            })->show($player);
    }
}