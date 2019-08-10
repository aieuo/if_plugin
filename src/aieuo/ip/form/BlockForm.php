<?php

namespace aieuo\ip\form;

use aieuo\ip\Session;
use aieuo\ip\form\Form;
use aieuo\ip\utils\Language;

class BlockForm {
    public function getSelectActionForm() {
        $data = [
            "type" => "form",
            "title" => Language::get("form.block.action.title"),
            "content" => Language::get("form.selectButton"),
            "buttons" => [
                Elements::getButton(Language::get("form.action.edit")),
                Elements::getButton(Language::get("form.action.check")),
                Elements::getButton(Language::get("form.action.delete")),
                Elements::getButton(Language::get("form.action.copy")),
                Elements::getButton(Language::get("form.cancel")),
                Elements::getButton(Language::get("form.back"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onSelectAction($player, $data) {
        if ($data === null) return;
        $session = Session::get($player);
        switch ($data) {
            case 0:
                $session->setData("action", "edit");
                $player->sendMessage(Language::get("form.block.action.edit"));
                break;
            case 1:
                $session->setData("action", "check");
                $player->sendMessage(Language::get("form.block.action.check"));
                break;
            case 2:
                $session->setData("action", "del");
                $player->sendMessage(Language::get("form.block.action.delete"));
                break;
            case 3:
                $session->setData("action", "copy");
                $player->sendMessage(Language::get("form.block.action.copy"));
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
        $session->setIfType(Session::BLOCK);
        $session->setValid();
    }
}