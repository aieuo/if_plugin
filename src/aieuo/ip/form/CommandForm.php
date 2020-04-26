<?php

namespace aieuo\ip\form;

use aieuo\ip\formAPI\CustomForm;
use aieuo\ip\formAPI\element\Button;
use aieuo\ip\formAPI\element\Dropdown;
use aieuo\ip\formAPI\element\Input;
use aieuo\ip\formAPI\element\Toggle;
use aieuo\ip\formAPI\ListForm;
use aieuo\ip\utils\Language;
use aieuo\ip\Session;
use aieuo\ip\IFPlugin;
use pocketmine\Player;

class CommandForm {
    public function sendSelectActionForm(Player $player) {
        (new ListForm("@form.command.action.title"))
            ->setContent("@form.selectButton")
            ->setButtons([
                new Button("@form.action.add"),
                new Button("@form.command.action.addOnlyCommand"),
                new Button("@form.action.edit"),
                new Button("@form.action.delete"),
                new Button("@form.command.action.commandList"),
                new Button("@form.cancel"),
                new Button("@form.back"),
            ])->onReceive(function (Player $player, int $data) {
                $session = Session::getSession($player);
                switch ($data) {
                    case 0:
                        $session->set("action", "add");
                        break;
                    case 1:
                        $session->set("action", "add_empty");
                        break;
                    case 2:
                        $session->set("action", "edit");
                        break;
                    case 3:
                        $session->set("action", "del");
                        break;
                    case 4:
                        $this->sendCommandListForm($player);
                        return;
                    case 5:
                        $session->setValid(false);
                        $player->sendMessage(Language::get("form.cancelled"));
                        return;
                    case 6:
                        (new Form())->sendSelectIfTypeForm($player);
                        return;
                }
                switch ($data) {
                    case 0:
                    case 1:
                        $this->sendAddCommandForm($player);
                        break;
                    case 2:
                    case 3:
                    case 4:
                        $this->sendSelectCommandForm($player);
                        break;
                }
                $session->setValid()->set("if_type", Session::COMMAND);
            })->show($player);

    }

    public function sendAddCommandForm(Player $player, array $default = [], array $errors = []) {
        (new CustomForm("@form.command.addCommand.title"))
            ->setContents([
                new Input("@form.command.addCommand.content0", "@form.command.addCommand.content0.placeholder", $default[0] ?? ""),
                new Input("@form.command.addCommand.content1", "", $default[1] ?? ""),
                new Dropdown("@form.command.permission", [Language::get("form.command.permission.op"), Language::get("form.command.permission.everyone")], $default[2] ?? 0),
                new Toggle("@form.cancel"),
            ])->onReceive(function (Player $player, array $data) {
                $session = Session::getSession($player);
                if ($data === null) {
                    $session->setValid(false, false);
                    return;
                }
                $manager = IFPlugin::getInstance()->getCommandManager();
                if ($data[3]) {
                    $this->sendSelectActionForm($player);
                    return;
                }
                if ($data[0] === "") {
                    $this->sendAddCommandForm($player, $data, [["@form.insufficient", 0]]);
                    return;
                }
                if ($manager->isRegisterd($data[0])) {
                    $this->sendAddCommandForm($player, $data, [["@form.command.alreadyInUse", 0]]);
                    return;
                }
                if ($manager->exists($data[0])) {
                    $this->sendAddCommandForm($player, $data, [["@form.command.alreadyExists", 0]]);
                    return;
                }
                if ($data[1] === "") $data[1] = Language::get("form.command.description.default");
                $manager->set($data[0], [], ["perm" => $data[2] == 0 ? "ifplugin.customcommand.op" : "ifplugin.customcommand.true", "desc" => $data[1], ]);
                $manager->register($data[0], $data[2] == 0 ? "ifplugin.customcommand.op" : "ifplugin.customcommand.true", $data[1]);
                if ($session->get("action") == "add_empty") {
                    $player->sendMessage(Language::get("form.command.added"));
                    $session->setValid(false);
                    return;
                }
                $session->set("if_key", $data[0]);
                $session->set("description", $data[1]);
                $session->set("permission", $data[2] == 0 ? "ifplugin.customcommand.op" : "ifplugin.customcommand.true");
                $ifData = $manager->repairIF([]);
                (new Form)->sendEditIfForm($player, $ifData);
            })->onClose(function (Player $player) {
                Session::getSession($player)->setValid(false, false);
            })->addErrors($errors)->show($player);
    }

    public function sendSelectCommandForm(Player $player, array $default = [], array $errors = []) {
        (new CustomForm("@form.command.selectCommand.title"))
            ->setContents([
                new Input("@form.command.selectCommand.name", "@form.command.selectCommand.name.placeholder", $default[0] ?? ""),
                new Toggle("@form.cancel"),
            ])->onReceive(function (Player $player, array $data) {
                $session = Session::getSession($player);
                if ($data[1]) {
                    $this->sendSelectActionForm($player);
                    return;
                }
                if ($data[0] === "") {
                    $this->sendSelectCommandForm($player, $data, [["@form.insufficient", 0]]);
                    return;
                }
                $manager = IFPlugin::getInstance()->getCommandManager();
                if (!$manager->exists($data[0])) {
                    $this->sendSelectCommandForm($player, $data, [["@form.command.notExists", 0]]);
                    return;
                }

                $session->set("if_key", $data[0]);
                $action = $session->get("action");
                if ($action == "edit") {
                    $ifData = $manager->get($data[0]);
                    (new Form)->sendEditIfForm($player, $ifData);
                } elseif ($action == "del") {
                    (new Form)->confirmDelete($player, [new Form(), "onDeleteIf"]);
                }
            })->onClose(function (Player $player) {
                Session::getSession($player)->setValid(false, false);
            })->addErrors($errors)->show($player);
    }

    public function sendCommandListForm(Player $player) {
        $manager = IFPlugin::getInstance()->getCommandManager();
        $commands = $manager->getAll();
        $buttons = [new Button("@form.back")];
        foreach ($commands as $command => $value) {
            $buttons[] = new Button($command);
        }
        (new ListForm("@form.command.list.title"))
            ->setContent("@form.selectButton")
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data) {
                $session = Session::getSession($player);
                if ($data === 0) {
                    $this->sendSelectActionForm($player);
                    return;
                }
                $manager = IFPlugin::getInstance()->getCommandManager();
                $command = key(array_slice($manager->getAll(), $data - 1, 1, true));
                $session->set("if_key", $command);
                $ifData = $manager->get($command);
                (new Form)->sendEditIfForm($player, $ifData);
            })->onClose(function (Player $player) {
                Session::getSession($player)->setValid(false, false);
            })->show($player);
    }
}
