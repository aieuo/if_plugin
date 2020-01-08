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
        $data = explode("[subtitle]", $content);
        $title = $data[0];
        $subtitles = $data[1] ?? "";
        return [$title, $subtitles];
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

    public function parseFormData(array $data) {
        $status = true;
        if ($data[1] === "" or $data[2] === "") {
            $status = null;
        } else {
            $names = $this->parse($data[1]."[subtitle]".$data[2]);
            if ($names === false) $status = false;
        }
        return ["status" => $status, "contents" => $data[1]."[subtitle]".$data[2], "delete" => $data[3], "cancel" => $data[4]];
    }
}