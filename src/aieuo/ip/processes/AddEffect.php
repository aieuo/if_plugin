<?php

namespace aieuo\ip\processes;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class AddEffect extends Process
{
	public $id = self::ADD_EFFECT;

	public function __construct($player = null, $effect = null)
	{
		parent::__construct($player);
		$this->setValues($effect);
	}

	public function getName()
	{
		return "エフェクトを与える";
	}

	public function getDescription()
	{
		return "プレイヤーにidが§7<id>§fで強さが§7<power>§fのエフェクトを§7<time>§f秒間与える";
	}

	public function getEffect()
	{
		return $this->getValues();
	}

	public function setEffect(EffectInstance $effect)
	{
		$this->setValues($effect);
	}

	public function parse(string $content)
	{
        $args = explode(",", $content);
        if(!isset($args[1]) or (int)$args[1] <= 0) $args[1] = 1;
        if(!isset($args[2]) or (float)$args[2] <= 0) $args[2] = 30;
		$effect = Effect::getEffectByName($args[0]);
        if($effect === null) $effect = Effect::getEffect((int)$args[0]);
        if($effect === null) return false;
		return new EffectInstance($effect, (float)$args[2] * 20, (int)$args[1], true);
	}

	public function toString() : string
	{
		$effect = $this->getEffect();
		if(!($effect instanceof EffectInstance)) return (string)$effect;
		$str = $effect->getId().",".$effect->getAmplifier().",".($effect->getDuration() * 20);
		return $str;
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$effect = $this->getEffect();
		if(!($effect instanceof EffectInstance))
		{
			if($effect === null) $player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			if($effect === false) $player->sendMessage("§c[".$this->getName()."] エフェクトが見つかりません");
			return;
		}
		$player->addEffect($effect);
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
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
			if($effect === null)$mes .= "§c正しく入力できていません§f";
			if($effect === false)$mes .= "§cエフェクトが見つかりません§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<id>§f エフェクトの名前かidを入力してください", "例) 1", $id),
                Elements::getInput("\n§7<power>§f エフェクトの強さを入力してください", "例) 5", $power),
                Elements::getInput("\n§7<time>§f エフェクトを与える時間を入力してください", "例) 5", $time),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}
}