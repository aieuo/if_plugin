<?php

namespace aieuo\ip\form;

use aieuo\ip\Session;
use aieuo\ip\form\BlockForm;
use aieuo\ip\form\Form;

class BlockForm {
    public function getSelectActionForm(){
        $data = [
            "type" => "form",
            "title" => "block > 操作選択",
            "content" => "§7ボタンを押してください",
            "buttons" => [
                [
                    "text" => "編集する"
                ],
                [
                    "text" => "確認する"
                ],
                [
                    "text" => "削除する"
                ],
                [
                    "text" => "コピーする"
                ],
                [
                    "text" => "キャンセルする"
                ],
                [
                    "text" => "ひとつ前の画面に戻る"
                ]
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onSelectAction($player, $data) {
        if($data === null) return;
        $session = $player->ifSession;
        switch ($data) {
            case 0:
                $session->setData("action", "edit");
                $player->sendMessage("編集するブロックを触ってください");
                break;
            case 1:
                $session->setData("action", "check");
                $player->sendMessage("確認するブロックを触ってください");
                break;
            case 2:
                $session->setData("action", "del");
                $player->sendMessage("削除するブロックを触ってください");
                break;
            case 3:
                $session->setData("action", "copy");
                $player->sendMessage("コピーするブロックを触ってください");
                break;
            case 4:
                $session->setValid(false);
                $player->sendMessage("キャンセルしました");
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