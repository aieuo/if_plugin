<?php

namespace aieuo\ip\form;

use aieuo\ip\ifPlugin;
use aieuo\ip\Session;
use aieuo\ip\utils\Messages;
use aieuo\ip\form\Form;

class CommandForm {
    public function getSelectActionForm() {
        $data = [
            "type" => "form",
            "title" => "command > 操作選択",
            "content" => "§7ボタンを押してください",
            "buttons" => [
                Elements::getButton("追加する"),
                Elements::getButton("コマンドだけ追加する"),
                Elements::getButton("編集する"),
                Elements::getButton("削除する"),
                Elements::getButton("追加したコマンド一覧"),
                Elements::getButton("キャンセルする"),
                Elements::getButton("ひとつ前の画面に戻る")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onSelectAction($player, $data) {
        if($data === null) return;
        $session = Session::get($player);
        switch ($data) {
            case 0:
                $session->setData("action", "add");
                break;
            case 1:
                $session->setData("action", "add_empty");
                break;
            case 2:
                $session->setData("action", "edit");
                break;
            case 3:
                $session->setData("action", "del");
                break;
            case 4:
                $form = $this->getCommandListForm();
                Form::sendForm($player, $form, $this, "onCommandList");
                return;
            case 5:
                $session->setValid(false);
                $player->sendMessage("キャンセルしました");
                return;
            case 6:
                $form = (new Form())->getSelectIfTypeForm();
                Form::sendForm($player, $form, new Form(), "onSelectIfType");
                return;
        }
        switch ($data) {
            case 0:
            case 1:
                $form = $this->getAddCommandForm();
                Form::sendForm($player, $form, $this, "onAddCommand");
                break;
            case 2:
            case 3:
            case 4:
                $form = $this->getSelectCommandForm();
                Form::sendForm($player, $form, $this, "onSelectCommand");
                break;
        }
        $session->setIfType(Session::COMMAND);
        $session->setValid();
    }


    public function getAddCommandForm($mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => "command > コマンド追加",
            "content" => [
                Elements::getInput(($mes !== "" ? $mes."\n" : "")."追加するコマンドの名前", "最初の/を外して"),
                Elements::getInput("コマンドの説明"),
                Elements::getDropdown("権限", ["opだけ", "全員使える"], 1),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onAddCommand($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        $manager = ifPlugin::getInstance()->getCommandManager();
        if($data[3]) {
            $form = $this->getSelectActionForm();
            Form::sendForm($player, $form, $this, "onSelectAction");
            return;
        }
        if($data[0] === ""){
            $form = $this->getAddCommandForm("§c必要事項を入力してください§f");
            Form::sendForm($player, $form, $this, "onAddCommand");
            $player->sendMessage("必要事項を入力してください");
            return;
        }
        if($manager->exists($data[0])){
            $form = $this->getAddCommandForm("§cそのコマンドは既に使用されています§f");
            Form::sendForm($player, $form, $this, "onAddCommand");
            $player->sendMessage("§cそのコマンドは既に使用されています");
            return;
        }
        if($manager->isAdded($data[0])){
            $form = $this->getAddCommandForm("§eそのコマンドは既に追加しています§f");
            Form::sendForm($player, $form, $this, "onAddCommand");
            $player->sendMessage("§eそのコマンドは既に追加しています");
            return;
        }
        if($data[1] === "") $data[1] = "ifPluginで追加したコマンドです";
        $manager->set($data[0], [], ["perm" => $data[2] == 0 ? "ifplugin.customcommand.op" : "ifplugin.customcommand.true", "desc" => $data[1], ]);
        $manager->register($data[0], $data[2] == 0 ? "ifplugin.customcommand.op" : "ifplugin.customcommand.true", $data[1]);
        if($session->getData("action") == "add_empty"){
            $player->sendMessage("追加しました");
            $session->setValid(false);
            return;
        }
        $session->setData("if_key", $data[0]);
        $session->setData("description", $data[1]);
        $session->setData("permission", $data[2] == 0 ? "ifplugin.customcommand.op" : "ifplugin.customcommand.true");
        $datas = $manager->repairIF([]);
        $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
        $form = (new Form)->getEditIfForm($mes);
        Form::sendForm($player, $form, new Form(), "onEditIf");
    }


    public function getSelectCommandForm($mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => "command > コマンド選択",
            "content" => [
                Elements::getInput(($mes !== "" ? $mes."\n" : "")."コマンドの名前", "最初の/を外して"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onSelectCommand($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        if($data[1]) {
            $form = $this->getSelectActionForm();
            Form::sendForm($player, $form, $this, "onSelectAction");
            return;
        }
        if($data[0] === ""){
            $form = $this->getSelectCommandForm("§c必要事項を入力してください§f");
            Form::sendForm($player, $form, $this, "onSelectCommand");
            $player->sendMessage("必要事項を入力してください");
            return;
        }
        $manager = ifPlugin::getInstance()->getCommandManager();
        if(!$manager->isAdded($data[0])){
            $form = $this->getSelectCommandForm("§cそのコマンドはまだ追加されていません§f");
            Form::sendForm($player, $form, $this, "onSelectCommand");
            $player->sendMessage("そのコマンドはまだ追加されていません");
            return;
        }

        $session->setData("if_key", $data[0]);
        $action = $session->getData("action");
        if($action == "edit"){
            $datas = $manager->get($data[0]);
            $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
            $form = (new Form)->getEditIfForm($mes);
            Form::sendForm($player, $form, new Form(), "onEditIf");
        }elseif($action == "del"){
            $form = (new Form())->getConfirmDeleteForm();
            Form::sendForm($player, $form, new Form(), "onDeleteIf");
        }
    }

    public function getCommandListForm() {
        $manager = ifPlugin::getInstance()->getCommandManager();
        $commands = $manager->getAll();
        $buttons = [Elements::getButton("<1つ前のページに戻る>")];
        foreach ($commands as $command => $value) {
            $buttons[] = Elements::getButton($command);
        }
        $data = [
            "type" => "form",
            "title" => "command > 操作選択",
            "content" => "§7ボタンを押してください",
            "buttons" => $buttons
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onCommandList($player, $data) {
        $session = Session::get($player);
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        if($data === 0) {
            $form = $this->getSelectActionForm();
            Form::sendForm($player, $form, $this, "onSelectAction");
            return;
        }
        $manager = ifPlugin::getInstance()->getCommandManager();
        $command = key(array_slice($manager->getAll(), $data - 1, 1, true));
        $session->setData("if_key", $command);
        $datas = $manager->get($command);
        $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
        $form = (new Form)->getEditIfForm($mes);
        Form::sendForm($player, $form, new Form(), "onEditIf");
    }
}