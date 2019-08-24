<?php

namespace aieuo\ip\form;

use aieuo\ip\Session;
use aieuo\ip\IFPlugin;
use aieuo\ip\IFAPI;
use aieuo\ip\utils\Language;

class ImportForm {
    public function getImportListForm($mes = "") {
        $buttons = [Elements::getButton(Language::get("form.back"))];
        $files = glob(IFPlugin::getInstance()->getDataFolder()."imports/*.json");
        foreach ($files as $file) {
            if (is_dir($file)) continue;
            $datas = json_decode(file_get_contents($file), true);
            $buttons[] = Elements::getButton($datas["name"]." | ".$datas["author"]);
        }
        $data = [
            "type" => "form",
            "title" => Language::get("form.import.selectFile.title"),
            "content" => ($mes === "" ? "" : $mes."\n").Language::get("form.selectButton"),
            "buttons" => $buttons
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onImportList($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        if ($data == 0) {
            $data = (new Form())->getSelectIfTypeForm();
            Form::sendForm($player, $data, new Form(), "onSelectIfType");
            return;
        }
        $files = glob(IFPlugin::getInstance()->getDataFolder()."imports/*.json");
        if (!isset($files[$data - 1])) {
            $form = $this->getImportListForm(Language::get("form.import.error"));
            Form::sendForm($player, $form, $this, "onImportList");
            return;
        }
        $path = $files[$data - 1];
        $session->set("path", $path);
        $form = $this->getImportForm(json_decode(file_get_contents($path), true));
        Form::sendForm($player, $form, $this, "onImport");
    }

    public function getImportForm($datas) {
        $mes = Language::get("form.import.import.content", [$datas["name"], $datas["author"], $datas["details"]]);
        foreach ($datas["ifs"] as $key => $value) {
            $mes .= "---------------------------\n";
            $mes .= "§l".$key."§r§f\n".IFAPI::createIFMessage($value["if"], $value["match"], $value["else"])."\n";
        }
        $data = [
            "type" => "custom_form",
            "title" => Language::get("form.import.import.title", $datas["name"]),
            "content" => [
                Elements::getLabel($mes),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function onImport($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        if ($data[1]) {
            $form = $this->getImportListForm(Language::get("form.cancelled"));
            Form::sendForm($player, $form, $this, "onImportList");
            return;
        }
        $file = json_decode(file_get_contents($session->get("path")), true);
        $this->importDatas($player, $file);
    }

    public function importDatas($player, $file, $count = 0) {
        $session = Session::getSession($player);
        foreach ($file["ifs"] as $key => $datas) {
            if ($datas["type"] === Session::BLOCK) {
                $manager = IFPlugin::getInstance()->getBlockManager();

                if ($manager->exists($key) and !isset($session->get("overwrite")[$key])) {
                    $session->set("file", $file);
                    $session->set("if_key", $key);
                    $session->set("count", $count);
                    $form = $this->getConfirmOverwriteForm($key);
                    Form::sendForm($player, $form, $this, "onConfirmOverwrite");
                    return;
                } elseif ($manager->exists($key) and !$session->get("overwrite")[$key]) {
                    continue;
                }

                $manager->set($key, [
                    "if" => $datas["if"],
                    "match" => $datas["match"],
                    "else" => $datas["else"],
                    "author" => $file["author"]
                ]);
                $count ++;
            } elseif ($datas["type"] === Session::COMMAND) {
                $manager = IFPlugin::getInstance()->getCommandManager();

                if (!$manager->exists($key) and $manager->isRegisterd($key)) continue;
                if ($manager->exists($key) and !isset($session->get("overwrite")[$key])) {
                    $session->set("file", $file);
                    $session->set("if_key", $key);
                    $session->set("count", $count);
                    $form = $this->getConfirmOverwriteForm($key);
                    Form::sendForm($player, $form, $this, "onConfirmOverwrite");
                    return;
                } elseif ($manager->exists($key) and !$session->get("overwrite")[$key]) {
                    continue;
                }

                $manager->set($key, [
                    "if" => $datas["if"],
                    "match" => $datas["match"],
                    "else" => $datas["else"],
                    "author" => $file["author"]
                ], [
                    "desc" => $datas["description"],
                    "perm" => $datas["permission"]
                ]);
                $manager->register($key, $datas["description"], $datas["permission"]);
                $count ++;
            } elseif ($datas["type"] === Session::EVENT) {
                $manager = IFPlugin::getInstance()->getEventManager();
                $manager->addByEvent($datas["options"]["eventname"], $datas + ["author" => $file["author"]]);
                $count ++;
            } elseif ($datas["type"] === Session::CHAIN) {
                $manager = IFPlugin::getInstance()->getChainManager();

                if ($manager->exists($key) and !isset($session->get("overwrite")[$key])) {
                    $session->set("file", $file);
                    $session->set("if_key", $key);
                    $session->set("count", $count);
                    $form = $this->getConfirmOverwriteForm($key);
                    Form::sendForm($player, $form, $this, "onConfirmOverwrite");
                    return;
                } elseif ($manager->exists($key) and !$session->get("overwrite")[$key]) {
                    continue;
                }

                $manager->set($key, [
                    "if" => $datas["if"],
                    "match" => $datas["match"],
                    "else" => $datas["else"],
                    "author" => $file["author"]
                ]);
                $count ++;
            }
            unset($file["ifs"][$key]);
        }
        $player->sendMessage(Language::get("form.import.success", [$count]));
        $session->setValid(false);
    }

    public function getConfirmOverwriteForm($name) {
        $data = [
            "type" => "modal",
            "title" => Language::get("form.import.overwriting.title"),
            "content" => Language::get("form.import.overwriting.content", [$name]),
            "button1" => Language::get("form.yes"),
            "button2" => Language::get("form.no")
        ];
        $data = Form::encodeJson($data);
        return $data;
    }

    public function onConfirmOverwrite($player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        if ($data) {
            if (($overwrite = $session->get("overwrite")) === "") {
                $session->set("overwrite", [$session->get("if_key") => true]);
            } else {
                $overwrite[$session->get("if_key")] = true;
                $session->set("overwrite", $overwrite);
            }
            $this->importDatas($player, $session->get("file"), $session->get("count"));
        } else {
            if (($overwrite = $session->get("overwrite")) === "") {
                $session->set("overwrite", [$session->get("if_key") => false]);
            } else {
                $overwrite[$session->get("if_key")] = false;
                $session->set("overwrite", $overwrite);
            }
            $this->importDatas($player, $session->get("file"), $session->get("count"));
        }
    }
}