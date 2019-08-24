<?php

namespace aieuo\ip\form;

use aieuo\ip\form\Form;
use aieuo\ip\Session;
use aieuo\ip\IFPlugin;
use aieuo\ip\IFAPI;
use aieuo\ip\utils\Language;

class ChainIfForm {
    public function getSelectActionForm() {
        $data = [
            "type" => "form",
            "title" => Language::get("form.chain.selectAction.title"),
            "content" => Language::get("form.selectButton"),
            "buttons" => [
                Elements::getButton(Language::get("form.action.add")),
                Elements::getButton(Language::get("form.action.edit")),
                Elements::getButton(Language::get("form.action.delete")),
                Elements::getButton(Language::get("form.chain.list")),
                Elements::getButton(Language::get("form.cancel")),
                Elements::getButton(Language::get("form.back"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onSelectAction($player, $data) {
        if ($data === null) return;
        $session = Session::getSession($player);
        switch ($data) {
            case 0:
                $session->set("action", "add");
                $form = $this->getAddChainIfForm();
                Form::sendForm($player, $form, $this, "onAddChainIf");
                break;
            case 1:
                $session->set("action", "edit");
                $form = $this->getEditChainIfForm();
                Form::sendForm($player, $form, $this, "onEditChainIf");
                break;
            case 2:
                $session->set("action", "del");
                $form = $this->getEditChainIfForm();
                Form::sendForm($player, $form, $this, "onEditChainIf");
                break;
            case 3:
                $form = $this->getChainIfListForm();
                Form::sendForm($player, $form, $this, "onChainIfList");
                break;
            case 4:
                $session->setValid(false);
                $player->sendMessage(Language::get("form.cancelled"));
                return;
            case 5:
                $form = (new Form())->getSelectIfTypeForm();
                Form::sendForm($player, $form, new Form(), "onSelectIfType");
                return;
        }
        $session->setValid()->set("if_type", Session::CHAIN);
    }

    public function getAddChainIfForm($mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => Language::get("form.chain.addChain.title"),
            "content" => [
                Elements::getInput(($mes !== "" ? $mes."\n" : "").Language::get("form.chain.addChain.content0"), ""),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onAddChainIf($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        if ($data[1]) {
            $form = $this->getSelectActionForm();
            Form::sendForm($player, $form, $this, "onSelectAction");
            return;
        }
        if ($data[0] === "") {
            $form = $this->getAddChainIfForm(Language::get("form.insufficient"));
            Form::sendForm($player, $form, $this, "onAddChainIf");
            $player->sendMessage(Language::get("form.insufficient"));
            return;
        }
        $manager = IFPlugin::getInstance()->getChainManager();
        if ($manager->exists($data[0])) {
            $form = $this->getAddChainIfForm(Language::get("form.chain.alreadyExists"));
            Form::sendForm($player, $form, $this, "onAddChainIf");
            $player->sendMessage(Language::get("form.chain.alreadyExists"));
            return;
        }
        $session->set("if_key", $data[0]);
        $datas = $manager->repairIF([]);
        $manager->set($data[0], $datas);
        $mes = IFAPI::createIFMessage($datas["if"], $datas["match"], $datas["else"]);
        $form = (new Form)->getEditIfForm($mes, $datas["name"] ?? null);
        Form::sendForm($player, $form, new Form(), "onEditIf");
    }

    public function getEditChainIfForm($mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => Language::get("form.chain.editChain.title"),
            "content" => [
                Elements::getInput(($mes !== "" ? $mes."\n" : "").Language::get("form.chain.editChain.content0"), ""),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onEditChainIf($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        if ($data[1]) {
            $form = $this->getSelectActionForm();
            Form::sendForm($player, $form, $this, "onSelectAction");
            return;
        }
        if ($data[0] === "") {
            $form = $this->getAddChainIfForm(Language::get("form.insufficient"));
            Form::sendForm($player, $form, $this, "onAddChainIf");
            $player->sendMessage(Language::get("form.insufficient"));
            return;
        }
        $manager = IFPlugin::getInstance()->getChainManager();
        if (!$manager->exists($data[0])) {
            $form = $this->getAddChainIfForm(Language::get("form.chain.notExists"));
            Form::sendForm($player, $form, $this, "onAddChainIf");
            $player->sendMessage(Language::get("form.chain.notExists"));
            return;
        }
        $session->set("if_key", $data[0]);
        $action = $session->get("action");
        if ($action === "edit") {
            $datas = $manager->repairIF($manager->get($data[0]));
            $mes = IFAPI::createIFMessage($datas["if"], $datas["match"], $datas["else"]);
            $form = (new Form)->getEditIfForm($mes, $datas["name"] ?? null);
            Form::sendForm($player, $form, new Form(), "onEditIf");
        } elseif ($action === "del") {
            $form = (new Form())->getConfirmDeleteForm();
            Form::sendForm($player, $form, new Form(), "onDeleteIf");
        }
    }

    public function getChainIfListForm() {
        $datas = IFPlugin::getInstance()->getChainManager()->getAll();
        $buttons = [Elements::getButton(Language::get("form.back"))];
        foreach ($datas as $name => $data) {
            $buttons[] = Elements::getButton($name);
        }
        $data = [
            "type" => "form",
            "title" => Language::get("form.chain.list.title"),
            "content" => Language::get("form.selectButton"),
            "buttons" => $buttons
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onChainIfList($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        if ($data === 0) {
            $form = $this->getSelectActionForm();
            Form::sendForm($player, $form, $this, "onSelectAction");
            return;
        }
        $manager = IFPlugin::getInstance()->getChainManager();
        $ifs = array_slice($manager->getAll(), $data-1, 1, true);
        $key = key($ifs);
        $datas = current($ifs);
        $session->set("if_key", $key);
        $mes = IFAPI::createIFMessage($datas["if"], $datas["match"], $datas["else"]);
        $form = (new Form)->getEditIfForm($mes, $datas["name"] ?? null);
        Form::sendForm($player, $form, new Form(), "onEditIf");
    }
}