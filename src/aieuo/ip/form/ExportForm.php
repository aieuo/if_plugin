<?php

namespace aieuo\ip\form;

use aieuo\ip\formAPI\CustomForm;
use aieuo\ip\formAPI\element\Input;
use aieuo\ip\formAPI\element\Toggle;
use aieuo\ip\manager\IFManager;
use aieuo\ip\Session;
use aieuo\ip\IFPlugin;
use aieuo\ip\IFAPI;
use aieuo\ip\utils\Language;
use pocketmine\Player;

class ExportForm {
    public function sendExportForm(Player $player, array $default = [], array $errors = []) {
        (new CustomForm("@if.export.title"))
            ->setContents([
                new Input("@if.export.form.content0", "", $default[0] ?? ""),
                new Input("@if.export.form.content1", "", $default[1] ?? ""),
                new Input("@if.export.form.content2", "", $default[2] ?? ""),
                new Toggle("@form.cancel"),
            ])->onReceive(function (Player $player, array $data) {
                $session = Session::getSession($player);
                $type = $session->get("if_type");
                $manager = IFManager::getBySession($session);
                $options = IFPlugin::getInstance()->getOptionsBySession($session);
                $key = $session->get("if_key");
                $ifData = $manager->get($key, $options);

                if ($data[3]) {
                    (new Form)->sendEditIfForm($player, $ifData);
                    return;
                }
                if ($data[0] == "" or $data[1] == "" or $data[2] == "") {
                    $this->sendExportForm($player, $data, [["@form.insufficient", 0]]);
                    return;
                }
                $ifData["type"] = $type;
                $ifData["options"] = $options;
                $export = [
                    "name" => $data[0],
                    "author" => $data[1],
                    "details" => $data[2],
                    "plugin_version" => IFPlugin::getInstance()->getDescription()->getVersion(),
                    "ifs" => [
                        $key => $ifData
                    ]
                ];
                $filename = $data[0]."_".$data[1]."_".$type."_".$key.".json";
                $path = IFPlugin::getInstance()->getDataFolder()."exports/".$filename;
                file_put_contents($path, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

                $player->sendMessage(Language::get("if.export.success", [$filename]));
                $session->setValid(false);
            })->onClose(function (Player $player) {
                Session::getSession($player)->setValid(false, false);
            })->addErrors($errors)->show($player);
    }
}