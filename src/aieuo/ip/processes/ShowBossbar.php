<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;
use aieuo\ip\utils\Bossbar;
use aieuo\ip\utils\Language;

class ShowBossbar extends Process {

    protected $id = self::SHOW_BOSSBAR;
    protected $name = "@process.showBossbar.name";
    protected $description = "@process.showBossbar.description";

    public function getDetail(): string {
        $title = $this->getTitle();
        $max = $this->getMaxValue();
        $current = $this->getCurrentValue();
        return Language::get("process.showBossbar.detail", [$title, $max, $current]);
    }

    public function getTitle(): ?string {
        return $this->getValues()[0] ?? null;
    }

    public function getMaxValue(): ?float {
        return $this->getValues()[1] ?? null;
    }

    public function getCurrentValue(): ?float {
        return $this->getValues()[2] ?? null;
    }

    public function getBossbarId(): ?string {
        return $this->getValues()[3] ?? null;
    }

    public function parse(string $content) {
        $titles = explode("[max]", $content);
        if (!isset($titles[1])) return false;
        $title = $titles[0];
        $values = explode("[value]", $titles[1]);
        if (!isset($values[1])) return false;
        $max = $values[0];
        $ids = explode("[id]", $values[1]);
        if (!isset($ids[1])) return false;
        $value = $ids[0];
        $id = $ids[1];
        return [$title, (float)$max, (float)$value, $id];
    }

    public function execute() {
        $player = $this->getPlayer();
        if ($this->getValues() === false) {
            $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            return;
        }
        Bossbar::add($player, $this->getBossbarId(), $this->getTitle(), $this->getMaxValue(), $this->getCurrentValue()/$this->getMaxValue());
    }

    public function getEditForm(string $default = "", string $mes = "") {
        $settings = $this->parse($default);
        $titles = explode("[max]", $default);
        $title = $titles[0];
        $values = explode("[value]", $titles[1] ?? $default);
        $max = $values[0];
        $ids = explode("[id]", $values[1] ?? $titles[1] ?? $default);
        $value = $ids[0];
        $id = $ids[1] ?? $values[1] ?? $titles[1] ?? $default;
        if ($settings !== false) {
            $title = $settings[0];
            $max = $settings[1];
            $value = $settings[2];
            $id = $settings[3];
        } elseif ($default !== "") {
            $mes .= Language::get("form.error");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.showBossbar.form.title"), Language::get("input.example", ["aieuo"]), $title),
                Elements::getInput(Language::get("process.showBossbar.form.max"), Language::get("input.example", ["100"]), $max),
                Elements::getInput(Language::get("process.showBossbar.form.value"), Language::get("input.example", ["1"]), $value),
                Elements::getInput(Language::get("process.showBossbar.form.id"), Language::get("input.example", ["aieuo"]), $id),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        $contents = $datas[1]."[max]".$datas[2]."[value]".$datas[3]."[id]".$datas[4];
        if ($datas[1] === "" or $datas[2] === "" or $datas[3] === "" or $datas[3] === "") {
            $status = null;
        } else {
            $result = $this->parse($contents);
            if ($result === false) $status = false;
        }
        return ["status" => $status, "contents" => $contents, "delete" => $datas[5], "cancel" => $datas[6]];
    }
}