<?php

namespace aieuo\ip\form;

use aieuo\ip\manager\IFManager;
use aieuo\ip\Session;
use aieuo\ip\IFPlugin;
use aieuo\ip\IFAPI;

class ExportForm {
    public function getExportForm($mes = "") {
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
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $type = $session->get("if_type");
        $manager = IFManager::getBySession($session);
        $options = IFPlugin::getInstance()->getOptionsBySession($session);
        $key = $session->get("if_key");
        $datas = $manager->get($key, $options);
        if ($data[3]) {
            $mes = IFAPI::createIFMessage($datas["if"], $datas["match"], $datas["else"]);
            $form = (new Form())->getEditIfForm($mes, $datas["name"] ?? null);
            Form::sendForm($player, $form, new Form(), "onEditIf");
            return;
        }
        if ($data[0] == "" or $data[1] == "" or $data[2] == "") {
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
            "plugin_version" => IFPlugin::getInstance()->getDescription()->getVersion(),
            "ifs" => [
                $key => $datas
            ]
        ];
        $filename = $data[0]."_".$data[1]."_".$type."_".$key.".json";
        $path = IFPlugin::getInstance()->getDataFolder()."exports/".$filename;
        file_put_contents($path, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $player->sendMessage($filename."として保存しました");
        $session->setValid(false);
    }
}