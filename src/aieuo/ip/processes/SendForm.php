<?php

namespace aieuo\ip\processes;

use aieuo\ip\IFPlugin;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\Session;
use aieuo\ip\utils\Language;
use pocketmine\Player;

class SendForm extends Process {

    protected $id = self::SEND_FORM;
    protected $name = "@process.sendform.name";
    protected $description = "@process.sendform.description";

    public function getDetail(): string {
        $name = $this->getFormName();
        return Language::get("process.sendform.detail", [$name]);
    }

    public function getFormName(): ?string {
        $name = $this->getValues();
        return is_string($name) ? $name : null;
    }

    public function setFormName(string $name) {
        $this->setValues($name);
    }

    public function execute() {
        $player = $this->getPlayer();
        $name = $this->getFormName();
        $manager = IFPlugin::getInstance()->getFormIFManager();
        if (!$manager->exists($name)) {
            $player->sendMessage(Language::get("process.sendform.notfound", [$this->getName()]));
            return;
        }
        $form = json_encode($manager->getForm($name, $this->replaceDatas));
        Session::getSession($player)->set("form_name", $name);
        Form::sendForm($player, $form, $this, "onReceive", false);
    }

    public function onReceive(Player $player, $data) {
        $session = Session::getSession($player);
        if ($data === null) {
            $session->setValid(false, false);
            return;
        }
        $formName = $session->get("form_name");
        $manager = IFPlugin::getInstance()->getFormIFManager();
        if (!$manager->exists($formName)) {
            $player->sendMessage(Language::get("process.sendform.notfound", [$this->getName()]));
            return;
        }
        $data1 = $manager->getIF($formName);
        $form = $manager->getForm($formName, $this->replaceDatas);
        foreach ($data1["ifs"] as $ifData) {
            $manager->executeIfMatchCondition(
                $player,
                $ifData["if"],
                $ifData["match"],
                $ifData["else"],
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
                Elements::getInput(Language::get("process.sendform.form.name"), Language::get("input.example", ["aieuo"]), $default),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $data) {
        $status = true;
        if ($data[1] === "") $status = null;
        return ["status" => $status, "contents" => $data[1], "delete" => $data[2], "cancel" => $data[3]];
    }
}