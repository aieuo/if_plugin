<?php

namespace aieuo\ip\form;

use aieuo\ip\ifPlugin;
use aieuo\ip\Session;
use aieuo\ip\utils\Messages;

class ExportForm {
    public function getExportForm($mes = ""){
        $data = [
            "type" => "custom_form",
            "title" => "共有用ファイル作成",
            "content" => [
                Elements::getInput(($mes === "" ? "" : $mes."\n")."タイトル"),
                Elements::getInput("作成者"),
                Elements::getInput("説明"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onExport($player, $data) {
        $session = $player->ifSession;
        if($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->getIfType();
        $manager = ifPlugin::getInstance()->getManagerBySession($session);
        $options = ifPlugin::getInstance()->getOptionsBySession($session);
        $key = $session->getData("if_key");
        $datas = $manager->get($key, $options);
        if($data[3]) {
            $mes = Messages::createMessage($datas["if"], $datas["match"], $datas["else"]);
            $form = (new Form())->getEditIfForm($mes);
            Form::sendForm($player, $form, new Form(), "onEditIf");
        	return;
        }
        if($data[0] == "" or $data[1] == "" or $data[2] == "") {
            $form = $this->getExportForm("§c必要事項を記入してください§f");
            Form::sendForm($player, $form, $this, "onExport");
            $player->sendMessage("必要事項を入力してください");
            return;
        }
        $datas["type"] = $type;
        $datas["options"] = $options;
        $export = [
        	"name" => $data[0],
        	"author" => $data[1],
        	"details" => $data[2],
        	"ifs" => [
        		$key => $datas
        	]
        ];
        $filename = $data[0]."_".$data[1]."_".$type."_".$key.".json";
        $path = ifPlugin::getInstance()->getDataFolder()."exports/".$filename;
        file_put_contents($path, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $player->sendMessage($filename."として保存しました");
        $session->setValid(false);
    }
}