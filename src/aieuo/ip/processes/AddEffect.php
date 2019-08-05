<?php

namespace aieuo\ip\processes;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

use aieuo\ip\utils\Language;
use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class AddEffect extends Process {

    protected $id = self::ADD_EFFECT;
    protected $name = "@process.addeffect.name";
    protected $description = "@process.addeffect.description";

    public function getMessage() {
        $effect = $this->getEffect();
        if (!($effect instanceof EffectInstance)) return false;
        return Language::get("process.addeffect.detail", [$effect->getId(), $effect->getAmplifier(), $effect->getDuration() * 20]);
    }

    public function getEffect() {
        return $this->getValues();
    }

    public function setEffect(EffectInstance $effect) {
        $this->setValues($effect);
    }

    public function parse(string $content) {
        $args = explode(",", $content);
        if (!isset($args[1]) or (int)$args[1] <= 0) $args[1] = 1;
        if (!isset($args[2]) or (float)$args[2] <= 0) $args[2] = 30;
        $effect = Effect::getEffectByName($args[0]);
        if ($effect === null) $effect = Effect::getEffect((int)$args[0]);
        if ($effect === null) return null;
        return new EffectInstance($effect, (float)$args[2] * 20, (int)$args[1], true);
    }

    public function execute() {
        $player = $this->getPlayer();
        $effect = $this->getEffect();
        if (!($effect instanceof EffectInstance)) {
            if ($effect === false) $player->sendMessage(Language::get("input.invalid", [$this->getName()]));
            if ($effect === null) $player->sendMessage(Language::get("process.addeffect.notfound"));
            return;
        }
        $player->addEffect($effect);
    }


    public function getEditForm(string $default = "", string $mes = "") {
        $effect = $this->parse($default);
        $id = $default;
        $power = "";
        $time = "";
        if ($effect instanceof EffectInstance) {
            $id = $effect->getId();
            $power = $effect->getAmplifier();
            $time = $effect->getDuration() * 20;
        } elseif ($default !== "") {
            if ($effect === false)$mes .= Language::get("form.error");
            if ($effect === null)$mes .= Language::get("process.addeffect.notfound");
        }
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput(Language::get("process.addeffect.form.id"), Language::get("form.example", ["1"]), $id),
                Elements::getInput(Language::get("process.addeffect.form.power"), Language::get("form.example", ["5"]), $power),
                Elements::getInput(Language::get("process.addeffect.form.time"), Language::get("form.example", ["5"]), $time),
                Elements::getToggle(Language::get("form.delete")),
                Elements::getToggle(Language::get("form.cancel"))
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
    }

    public function parseFormData(array $datas) {
        $status = true;
        $effect_str = $datas[1].",".$datas[2].",".$datas[3];
        if ($datas[1] === "" or $datas[2] === "" or $datas[3] === "") $status = null;
        return ["status" => $status, "contents" => $effect_str, "delete" => $datas[4], "cancel" => $datas[5]];
    }
}