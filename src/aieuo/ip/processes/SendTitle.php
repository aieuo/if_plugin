<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Elements;
use aieuo\ip\form\Form;
use aieuo\ip\utils\Language;

class SendTitle extends Process {

    protected $id = self::SENDTITLE;
    protected $name = "@process.sendtitle.name";
    protected $description = "@process.sendtitle.description";

    public function getDetail(): string {
        return Language::get("process.sendtitle.detail", [$this->getTitle(), $this->getSubTitle()]);
    }

    public function getTitle(): ?string {
        $message = $this->getValues()[0];
        if (!is_string($message)) return null;
        return $message;
    }

    public function getSubTitle(): ?string {
        $message = $this->getValues()[1];
        if (!is_string($message)) return null;
        return $message;
    }

    public function parse(string $content) {
        $datas = explode("[subtitle]", $content);
        $title = $datas[0];
        $subtitls = $datas[1] ?? "";
        return [$title, $subtitls];
    }

    public function execute() {
        $player = $this->getPlayer();
        $player->addTitle($this->getTitle(), $this->getSubTitle());
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $messages = $this->parse($default);
        $title = $messages[0];
        $subtitle = $messages[1];
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.sendtitle.form.title"), Language::get("input.example", ["aieuo"]), $title),
                Elements::getInput(Language::get("process.sendtitle.form.subtitle"), Language::get("input.example", ["aiueo"]), $subtitle),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        if ($datas[1] === "" or $datas[2] === "") {
            $status = null;
        } else {
            $names = $this->parse($datas[1]."[subtitle]".$datas[2]);
            if ($names === false) $status = false;
        }
        return ["status" => $status, "contents" => $datas[1]."[subtitle]".$datas[2], "delete" => $datas[3], "cancel" => $datas[4]];
    }
}