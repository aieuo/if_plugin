<?php

namespace aieuo\ip\processes;

use pocketmine\event\entity\EntityDamageEvent;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class Attack extends Process {

	protected $id = self::ATTACK;
    protected $name = "ダメージを与える";
    protected $description = "プレイヤーにダメージを§7<damage>§f与える";

	public function getMessage() {
		$damage = $this->getDamage();
		if($damage === false) return false;
		return "プレイヤーに".$damage."ダメージ与える";
	}

	public function getDamage() {
		return $this->getValues();
	}

	public function setDamage(float $damage) {
		$this->setValues($damage);
	}

	public function parse(string $content) {
		$damage = (float)$content;
		if($damage <= 0) return false;
		return $damage;
	}

	public function execute() {
		$player = $this->getPlayer();
		$damage = $this->getDamage();
		if($damage === false) {
			$player->sendMessage("§c[".$this->getName()."] 攻撃力は1以上にしてください");
			return;
		}
		$event = new EntityDamageEvent($player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage);
		$player->attack($event);
	}

	public function getEditForm(string $default = "", string $mes = "") {
		$damage = $this->parse($default);
		if($damage === false) {
			if($default !== "") $mes .= "§c攻撃力は1以上にしてください§f";
			$damage = $default;
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<damage>§f 攻撃力を入力してください", "例) 5", $damage),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	$status = true;
    	if($datas[1] === "") {
    		$status = null;
    	} else {
	    	$damage = $this->parse($datas[1]);
	    	if($damage === false) $status = false;
	    }
    	return ["status" => $status, "contents" => $datas[1], "delete" => $datas[2], "cancel" => $datas[3]];
    }
}