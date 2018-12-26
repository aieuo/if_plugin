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
		return "プレイヤーにidが§7<id>§fで強さが§7<power>§rのエフェクトを§7<time>§r秒間与える";
	}

	public function getEditForm(string $defaults = "", string $mes = "")
	{
		$effect = $this->parse($defaults);
		$id = $defaults;
		$power = "";
		$time = "";
		if($effect instanceof EffectInstance)
		{
			$id = $effect->getId();
			$power = $effect->getAmplifier();
			$time = $effect->getDuration() / 20;
		}
		if($mes !== "") $mes = "\n".$mes;
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().$mes),
                Elements::getInput("<id>\nエフェクトの名前かidを入力してください", "例) 1", $id),
                Elements::getInput("<power>\nエフェクトの強さを入力してください", "例) 5", $power),
                Elements::getInput("<time>\nエフェクトを与える時間を入力してください", "例) 5", $time),
                Elements::getToggle("削除する")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

	public function parse(string $id)
	{
        $args = explode(",", $content);
        if(!isset($args[1]) or (int)$args[1] <= 0) $args[1] = 1;
        if(!isset($args[2]) or (float)$args[2] <= 0) $args[2] = 30;
		$effect = Effect::getEffectByName($args[0]);
        if($effect === null)
        {
            $effect = Effect::getEffect((int)$args[0]);
        }
        if($effect === null) return false;
		return new EffectInstance($effect, (float)$args[2] * 20, (int)$args[1], true);
	}

	public function getEffect() : ?EffectInstance
	{
		return $this->getValues();
	}

	public function setEffect(EffectInstance $effect)
	{
		$this->setValues($effect);
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$effect = $this->getEffect();
		if(!($effect instanceof EffectInstance))
		{
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
		$player->addEffect($effect);
	}
}