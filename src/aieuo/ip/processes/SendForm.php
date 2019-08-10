<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\Session;

class SendForm extends Process {

    protected $id = self::SEND_FORM;
    protected $name = "フォームを送信する";
    protected $description = "プレイヤーに§7<name>§fという名前のフォームを送信する";

    public function getMessage() {
        $name = $this->getFormName();
        return "プレイヤーに".$name."という名前のフォームを送信する";
    }

    public function getFormName() {
        return $this->getValues();
    }

    public function setFormName(string $name) {
        $this->setValues($name);
    }

    public function execute() {
        $player = $this->getPlayer();
        $name = $this->getFormName();
        $manager = ifPlugin::getInstance()->getFormIFManager();
        if (!$manager->isAdded($name)) {
            $player->sendMessage("§c[".$this->getName()."] その名前のフォームは存在しません");
            return;
        }
        $form = $manager->getIF($name)["form"];
        Session::get($player)->setData("form_name", $name);
        Form::sendForm($player, $form, $this, "onRecive");
    }

    public function onRecive($player, $data) {
        $session = Session::get($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $formName = $session->getData("form_name");
        $manager = ifPlugin::getInstance()->getFormIFManager();
        if (!$manager->isAdded($formName)) {
            $player->sendMessage("§c[".$this->getName()."] その名前のフォームは存在しません");
            return;
        }
        $datas = $manager->getIF($formName);
        $form = json_decode($datas["form"], true);
        foreach ($datas["ifs"] as $ifdata) {
            $manager->executeIfMatchCondition(
                $player,
                $ifdata["if"],
                $ifdata["match"],
                $ifdata["else"],
                [
                    "player" => $player,
                    "form" => $form,
                    "form_name" => $formName,
                    "form_data" => $data,
                ]
            );
        }
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<name>§f フォームの名前", "例) aieuo", $default),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        if ($datas[1] === "") $status = null;
        return ["status" => $status, "contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}