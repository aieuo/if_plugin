<?php

namespace aieuo\ip\processes;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class AddEffect extends Process {

	protected $id = self::ADD_EFFECT;
    protected $name = "エフェクトを与える";
    protected $description = "プレイヤーにidが§7<id>§fで強さが§7<power>§fのエフェクトを§7<time>§f秒間与える";

	public function getMessage() {
		$effect = $this->getEffect();
		if(!($effect instanceof EffectInstance)) return false;
		return "idが".$effect->getId()."で強さが".$effect->getAmplifier()."のエフェクトを".($effect->getDuration() * 20)."秒間与える";
	}

	public function getEffect() {
		return $this->getValues();
	}

	public function setEffect(EffectInstance $effect) {
		$this->setValues($effect);
	}

	public function parse(string $content) {
        $args = explode(",", $content);
        if(!isset($args[1]) or (int)$args[1] <= 0) $args[1] = 1;
        if(!isset($args[2]) or (float)$args[2] <= 0) $args[2] = 30;
		$effect = Effect::getEffectByName($args[0]);
        if($effect === null) $effect = Effect::getEffect((int)$args[0]);
        if($effect === null) return null;
		return new EffectInstance($effect, (float)$args[2] * 20, (int)$args[1], true);
	}

	public function execute() {
		$player = $this->getPlayer();
		$effect = $this->getEffect();
		if(!($effect instanceof EffectInstance))
		{
			if($effect === false) $player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			if($effect === null) $player->sendMessage("§c[".$this->getName()."] エフェクトが見つかりません");
			return;
		}
		$player->addEffect($effect);
	}


	public function getEditForm(string $default = "", string $mes = "") {
		$effect = $this->parse($default);
		$id = $default;
		$power = "";
		$time = "";
		if($effect instanceof EffectInstance)
		{
			$id = $effect->getId();
			$power = $effect->getAmplifier();
			$time = $effect->getDuration() * 20;
		}
		elseif($default !== "")
		{
			if($effect === false)$mes .= "§c正しく入力できていません§f";
			if($effect === null)$mes .= "§cエフェクトが見つかりません§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<id>§f エフェクトの名前かidを入力してください", "例) 1", $id),
                Elements::getInput("\n§7<power>§f エフェクトの強さを入力してください", "例) 5", $power),
                Elements::getInput("\n§7<time>§f エフェクトを与える時間を入力してください", "例) 5", $time),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	$status = true;
    	$effect_str = $datas[1].",".$datas[2].",".$datas[3];
    	if($datas[1] === "" or $datas[2] === "" or $datas[3] === "") $status = null;
    	return ["status" => $status, "contents" => $effect_str, "delete" => $datas[4], "cancel" => $datas[5]];
    }
}