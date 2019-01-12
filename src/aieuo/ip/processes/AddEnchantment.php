<?php

namespace aieuo\ip\processes;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class AddEnchantment extends Process
{
	public $id = self::ADD_ENCHANTMENT;

	public function __construct($player = null, $ehcnant = null)
	{
		parent::__construct($player);
		$this->setValues($ehcnant);
	}

	public function getName()
	{
		return "手に持ってるアイテムにエンチャントを追加する";
	}

	public function getDescription()
	{
		return "手に持ってるアイテムにidが§7<id>§fで強さが§7<power>§fのエンチャントを追加する";
	}

	public function getEnchantment()
	{
		return $this->getValues();
	}

	public function setEnchantment(EnchantmentInstance $enchant)
	{
		$this->setValues($enchant);
	}

	public function parse(string $content)
	{
        $args = explode(",", $content);
        if(!isset($args[1]) or (int)$args[1] <= 0) $args[1] = 1;
        if(is_numeric($args[0]))
        {
            $enchantment = Enchantment::getEnchantment((int)$args[0]);
        }
        else
        {
            $enchantment = Enchantment::getEnchantmentByName($args[0]);
        }
        if(!($enchantment instanceof Enchantment)) return null;
        return new EnchantmentInstance($enchantment, (int)$args[1]);
	}

	public function toString() : string
	{
		$enchant = $this->getEnchantment();
		if(!($enchant instanceof EnchantmentInstance)) return (string)$enchant;
		$str = $enchant->getId().",".$enchant->getLevel();
		return $str;
	}

	public function execute()
	{
		$player = $this->getPlayer();
		$enchant = $this->getEnchantment();
		if(!($enchant instanceof EnchantmentInstance))
		{
			if($enchant === false) $player->sendMessage("§c[".$this->getName()."] 正しく入力できていません");
			if($enchant === null) $player->sendMessage("§c[".$this->getName()."] エンチャントが見つかりません");
			return;
		}
		$item = $player->getInventory()->getItemInHand();
        $item->addEnchantment($enchant);
		$player->getInventory()->setItemInHand($item);
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
		$enchant = $this->parse($default);
		$id = $default;
		$power = "";
		if($enchant instanceof EnchantmentInstance)
		{
			$id = $enchant->getId();
			$power = $enchant->getLevel();
		}
		elseif($default !== "")
		{
			if($enchant === false) $mes .= "§c正しく入力できていません§f";
			if($enchant === null) $mes .= "§cエンチャントが見つかりません§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<id>§f エンチャントの名前かidを入力してください", "例) 1", $id),
                Elements::getInput("\n§7<power>§f エンチャントのレベルを入力してください", "例) 5", $name),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	if($datas[1] === "" or $datas[2] === "") return null;
    	$enchant_str = $datas[1].",".$datas[2];
    	return ["contents" => $enchant_str, "delete" => $datas[3], "cancel" => $datas[4]];
    }
}