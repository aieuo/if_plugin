<?php

namespace aieuo\ip\processes;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetScale extends Process {

    protected $id = self::SET_SCALE;
    protected $name = "プレイヤーのサイズを変える";
    protected $description = "プレイヤーのサイズを§7<scale>§fにする";

	public function getMessage() {
		$scale = $this->getScale();
		if($scale === false) return false;
		return "プレイヤーのサイズを".$scale."にする";
	}

	public function getScale() {
		return $this->getValues();
	}

	public function setScale(float $scale) {
		$this->setValues($scale);
	}

	public function parse(string $content) {
		$scale = (float)$content;
		if($scale <= 0) return false;
        return $scale;
	}

	public function execute() {
		$player = $this->getPlayer();
		$scale = $this->getScale();
		if($slace === false) {
			$player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			return;
		}
		$item = $player->getInventory()->getItemInHand();
        $item->addEnchantment($enchant);
		$player->getInventory()->setItemInHand($item);
	}


	public function getEditForm(string $default = "", string $mes = "") {
		$scale = $this->parse($default);
		if($scale === false and $default !== "") {
			$scale = $default;
			$mes .= "§c正しく入力できていません\n値は0より大きくしてください§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<scale>§f 大きさを入力してください", "例) 2", $scale),
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
	    	$scale = $this->parse($datas[1]);
	    	if($scale === false) $status = false;
	    }
    	return ["status" => $status, "contents" => $datas[1], "delete" => $datas[4], "cancel" => $datas[5]];
    }
}