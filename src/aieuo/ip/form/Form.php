<?php

namespace aieuo\ip\form;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use aieuo\ip\ifPlugin;
use aieuo\ip\ifAPI;
use aieuo\ip\utils\Messages;

class Form {

    private static $formId;

    public static function sendForm($player, $data, $id){
        $pk = new ModalFormRequestPacket();
        $pk->formId = $id;
        $pk->formData = $data;
        $player->dataPacket($pk);
    }

    public static function registerFormId($name){
        self::$formId[$name] = mt_rand(1, 999999999);
    }

    public static function registerFormIds(){
        self::registerFormId("SelectIfTypeForm");
        self::registerFormId("SelectIfForm");
        self::registerFormId("SelectBlockActionForm");
        self::registerFormId("EditIfForm");
        self::registerFormId("EditIfContentsForm");
        self::registerFormId("AddContentsForm");
        self::registerFormId("UpdateContentsForm");
        self::registerFormId("DetailForm");
        self::registerFormId("AddIfForm");
        self::registerFormId("InputContentsForm");
        self::registerFormId("SelectCommandActionForm");
        self::registerFormId("AddCommandForm");
        self::registerFormId("SelectCommandForm");
        self::registerFormId("SelectEventActionForm");
        self::registerFormId("SelectEventForm");
        self::registerFormId("EditEventForm");
    }

    public static function getFormId($type){
        return self::$formId[$type];
    }

////////////////////////////////block////////////////////////////////
    public static function getSelectBlockActionForm(){
        $data = [
            "type" => "form",
            "title" => "選択",
            "content" => "§7ボタンを押してください",
            "buttons" => [
                [
                    "text" => "追加する"
                ],
                [
                    "text" => "空の物を追加する",
                ],
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
                    "text" => "キャンセルする"
                ]
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }
////////////////////////////////command////////////////////////////////
    public static function getSelectCommandActionForm(){
        $data = [
            "type" => "form",
            "title" => "選択",
            "content" => "§7ボタンを押してください",
            "buttons" => [
                [
                    "text" => "追加する"
                ],
                [
                    "text" => "コマンドだけ追加する",
                ],
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
                    "text" => "キャンセルする"
                ]
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }
    public static function getAddCommandForm(){
        $data = [
            "type" => "custom_form",
            "title" => "追加",
            "content" => [
                Elements::getInput("追加するコマンドの名前", "最初の/を外して"),
                Elements::getInput("コマンドの説明"),
                Elements::getDropdown("権限", ["opだけ", "全員使える"], 1)
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }
    public static function getSelectCommandForm(){
        $data = [
            "type" => "custom_form",
            "title" => "コマンド選択",
            "content" => [
                Elements::getInput("コマンドの名前", "最初の/を外して"),
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }
////////////////////////////////event////////////////////////////////
    public static function getSelectEventActionForm(){
        $data = [
            "type" => "form",
            "title" => "選択",
            "content" => "§7ボタンを押してください",
            "buttons" => [
                [
                    "text" => "追加する"
                ],
                [
                    "text" => "空の物を追加する",
                ],
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
                    "text" => "キャンセルする"
                ]
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }
    public static function getSelectEventForm(){
        $data = [
            "type" => "custom_form",
            "title" => "イベント選択",
            "content" => [
                Parts::getEventListDropdown()
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }

    public static function getEditEventForm($event, $datas){
        $data = [
            "type" => "form",
            "title" => $event,
            "content" => "イベントの編集",
            "buttons" => []
        ];
        foreach ($datas as $key => $value) {
            $mes = Messages::createMessage($value["if"], $value["match"], $value["else"]);
            $data["buttons"][] = ["text" => mb_substr(str_replace("\n", " ", $mes), 0, 30)."・・・"];
        }
        $data["buttons"][] = ["text" => "追加する"];
        $json = self::encodeJson($data);
        return $json;
    }
////////////////////////////////all////////////////////////////////
    public static function getSelectIfTypeForm(){
        $data = [
            "type" => "form",
            "title" => "選択",
            "content" => "§7ボタンを押してください",
            "buttons" => [
                [
                    "text" => "ブロック"
                ],
                [
                    "text" => "コマンド",
                ],
                [
                    "text" => "イベント"
                ]
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }

    public static function getEditIfForm($mes){
        $data = [
            "type" => "form",
            "title" => "編集",
            "content" => $mes,
            "buttons" => [
                [
                    "text" => "もし~を編集する"
                ],
                [
                    "text" => "条件に合った時を編集する",
                ],
                [
                    "text" => "条件に合わなかった時を編集する"
                ],
                [
                    "text" => "削除する"
                ]
            ]
        ];
        $data = self::encodeJson($data);
        return $data;
    }
    public static function getEditContentsForm($datas){
        $data = [
            "type" => "form",
            "title" => "編集",
            "content" => "",
            "buttons" => []
        ];
        $data["buttons"][] = ["text" => "追加する"];
        foreach ($datas as $key => $value) {
            $data["buttons"][] = ["text" => Messages::getMessage($value["id"], $value["content"])];
        }
        $data = self::encodeJson($data);
        return $data;
    }

    public static function getDetailForm($id, $content){
        $data = [
            "type" => "form",
            "title" => "詳細",
            "content" => Messages::getMessage($id, $content),
            "buttons" => [
                ["text" => "編集する"],
                ["text" => "削除する"]
            ]
        ];
        $data = self::encodeJson($data);
        return $data;
    }

    public static function getSelectIfForm($ifs){
        $buttons = [];
        foreach ($ifs as $if){
            $buttons[] = ["text" => $if];
        }
        $data = [
            "type" => "form",
            "title" => "選択",
            "content" => "§7ボタンを押してください",
            "buttons" => $buttons
        ];
        $json = self::encodeJson($data);
        return $json;
    }

    public static function getAddContentsForm($type, $event = false){
        if($type == "if"){
            $list = Parts::getIflistDropdown();
        }else{
            $list = Parts::getExelistDropdown(0, $event);
        }
        $data = [
            "type" => "custom_form",
            "title" => "追加",
            "content" => [
                $list,
                Elements::getInput("値を入力して下さい")
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }

    public static function getUpdateContentsForm($type, $id, $defaltInput = ""){
        if($type == "if"){
            $mes = Parts::getIflist()[ifAPI::getListNumberByIfId($id)];
        }else{
            $mes = Parts::getExelist()[ifAPI::getListNumberByExeId($id)];
        }
        $data = [
            "type" => "custom_form",
            "title" => "追加",
            "content" => [
                Elements::getLabel($mes),
                Elements::getInput("値を入力して下さい", "", $defaltInput)
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }

    public static function getAddIfForm($event = false){
        $data = [
            "type" => "custom_form",
            "title" => "追加",
            "content" => [
                Parts::getIflistDropdown(),
                Parts::getExelistDropdown(0, $event),
                Parts::getExelistDropdown(0, $event)
            ]
        ];
        $json = self::encodeJson($data);
        return $json;
    }
//////////////////////////////////////////////////////////
    public static function createIfContentForm($type1, $type2, $type3, $encode = true, $event = false){
        $messages1 = Messages::getFormMessage($type1, $event);//もし
        $messages2 = Messages::getFormMessage($type2);//合ったら
        $messages3 = Messages::getFormMessage($type3);//合わなかったら

        if($messages1["type"] == "input"){
            $input1 = Elements::getInput($messages1["message"], $messages1["placeholder"]);
        }else{
            $input1 = Elements::getLabel($messages1["message"]);
        }
        if($messages2["type"] == "input"){
            $input2 = Elements::getInput($messages1["next"].$messages2["message"], $messages2["placeholder"]);
        }else{
            $input2 = Elements::getLabel($messages2["message"]);
        }
        if($messages3["type"] == "input"){
            $input3 = Elements::getInput($messages2["next"]."合わなかったら\n".$messages3["message"], $messages3["placeholder"]);
        }else{
            $input3 = Elements::getLabel("合わなかったら\n".$messages3["message"]);
        }

        $content[] = $input1;
        $content[] = $input2;
        $content[] = $input3;

        $content[] = Elements::getLabel($messages3["next"]);

        $data = [
            "type" => "custom_form",
            "title" => "ifPlugin",
            "content" => $content
        ];
        if($encode)$data = self::encodeJson($data);
        return $data;
    }

//////////////////////////////////////////////////////////////
    public static function encodeJson($data){
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
        return $json;
    }
}