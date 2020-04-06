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
        return Language::get("process.sendtitle.detail", [$this->getTitle(), $this->getSubTitle(), $this->getFadeIn(), $this->getStay(), $this->getFadeOut()]);
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

    public function getFadeIn(): int {
        return $this->getValues()[2] ?? -1;
    }

    public function getStay(): int {
        return $this->getValues()[3] ?? -1;
    }

    public function getFadeOut(): int {
        return $this->getValues()[4] ?? -1;
    }

    public function parse(string $content) {
        $titles = explode("[subtitle]", $content);
        $title = $titles[0];
        $subtitles = explode("[fadeIn]", $titles[1] ?? "");
        $subtitle = $subtitles[0];
        $fadeIns = explode("[stay]", $subtitles[1] ?? "10[stay]20[fadeOut]10");
        $fadeIn = $fadeIns[0];
        $stays = explode("[fadeOut]", $fadeIns[1] ?? "20[fadeOut]10");
        $stay = $stays[0];
        $fadeOut = $stays[1];
        return [$title, $subtitle, (int)$fadeIn, (int)$stay, (int)$fadeOut];
    }

    public function execute() {
        $player = $this->getPlayer();
        $player->addTitle($this->getTitle(), $this->getSubTitle(), $this->getFadeIn(), $this->getStay(), $this->getFadeOut());
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $messages = $this->parse($default);
        $title = $messages[0];
        $subtitle = $messages[1];
        $fadeIn = (string)$messages[2];
        $stay = (string)$messages[3];
        $fadeOut = (string)$messages[4];
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.sendtitle.form.title"), Language::get("input.example", ["aieuo"]), $title),
                Elements::getInput(Language::get("process.sendtitle.form.subtitle"), Language::get("input.example", ["aiueo"]), $subtitle),
                Elements::getInput(Language::get("process.sendtitle.form.fadeIn"), Language::get("input.example", ["-1"]), $fadeIn),
                Elements::getInput(Language::get("process.sendtitle.form.stay"), Language::get("input.example", ["-1"]), $stay),
                Elements::getInput(Language::get("process.sendtitle.form.fadeOut"), Language::get("input.example", ["-1"]), $fadeOut),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        return Form::encodeJson($data);
    }

    public function parseFormData(array $data) {
        $status = true;
        if ($data[1] === "" or $data[2] === "") {
            $status = null;
        }
        for ($i=3; $i<=5; $i++) {
            if ($data[$i] === "") $data[$i] = "-1";
        }
        $content = $data[1]."[subtitle]".$data[2]."[fadeIn]".$data[3]."[stay]".$data[4]."[fadeOut]".$data[5];
        return ["status" => $status, "contents" => $content, "delete" => $data[6], "cancel" => $data[7]];
    }
}