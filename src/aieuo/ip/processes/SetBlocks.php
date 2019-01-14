<?php

namespace aieuo\ip\processes;

use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\block\Block;

use aieuo\ip\form\Form;
use aieuo\ip\form\Elements;

class SetBlocks extends TypePosition {
	public $id = self::SET_BLOCKS;

	public function __construct($player = null, $settings = false)
	{
		parent::__construct($player);
		$this->setValues($settings);
	}

	public function getName()
	{
		return "指定した範囲にブロックを設置する";
	}

	public function getDescription()
	{
		return "§7<level>§fワールドの§7<spos>§f~§7<epos>§fに§7<id>§fのブロックを設置する";
	}

	public function getMessage() {
		if($this->getValues() === false) return false;
		$spos = $this->getStartPosition();
		$epos = $this->getEndPosition();
		$level = $this->getLevel();
		$block = $this->getBlock();
		$sx = min($spos->x, $epos->x);
		$sy = min($spos->y, $epos->y);
		$sz = min($spos->z, $epos->z);
		$ex = max($spos->x, $epos->x);
		$ey = max($spos->y, $epos->y);
		$ez = max($spos->z, $epos->z);
		return $level->getFolderName()."の".$sx.",".$sy.",".$sz."～".$ex.",".$ey.",".$ez."に".$block->getId().":".$block->getDamage()."を設置する";
	}

	public function getStartPosition() {
		return $this->getValues()[0];
	}

	public function getEndPosition() {
		return $this->getValues()[1];
	}

	public function getLevel() {
		return $this->getValues()[2];
	}

	public function getBlock() {
		return $this->getValues()[3];
	}

	public function setSettings(Vector3 $spos, Vector3 $epos, Level $level, Block $block) {
		$this->setValues([$spos, $epos, $level, $block]);
	}

	public function parse(string $content)
	{
        $settings = explode(";", $content);
        if(!isset($settings[3])) return false;
        $spos = parent::parse($settings[0]);
        if(!($spos instanceof Vector3)) return false;
        $epos = parent::parse($settings[1]);
        if(!($epos instanceof Vector3)) return false;
        $level = Server::getInstance()->getLevelByName($settings[2]);
        if(!($level instanceof Level)) return false;
        $ids = explode(":", $settings[3]);
        $block = Block::get($ids[0], isset($ids[1]) ? $ids[1] : 0);
        return [$spos, $epos, $level, $block];
	}

	public function execute()
	{
		if($this->getValues() === false) return false;
		$spos = $this->getStartPosition();
		$epos = $this->getEndPosition();
		$level = $this->getLevel();
		$block = $this->getBlock();
		$sx = min($spos->x, $epos->x);
		$sy = min($spos->y, $epos->y);
		$sz = min($spos->z, $epos->z);
		$ex = max($spos->x, $epos->x);
		$ey = max($spos->y, $epos->y);
		$ez = max($spos->z, $epos->z);
		for($x = $sx; $x <= $ex; $x ++) {
			for($y = $sy; $y <= $ey; $y ++) {
				for($z = $sz; $z <= $ez; $z ++) {
					$level->setBlock(new Vector3($x, $y, $z), $block);
				}
			}
		}
	}


	public function getEditForm(string $default = "", string $mes = "")
	{
		$settings = $this->parse($default);
		$spos_str = $default;
		$epos_str = "";
		$level_str = "";
		$id = "";
		if($settings !== false) {
			$spos = $settings[0];
			$spos_str = $spos->x.",".$spos->y.",".$spos->z;
			$epos = $settings[1];
			$epos_str = $epos->x.",".$epos->y.",".$epos->z;
			$level = $settings[2];
			$level_str = $level->getFolderName();
			$block = $settings[3];
			$id = $block->getId().":".$block->getDamage();
		} elseif($default !== "") {
			$mes .= "§c正しく入力できていません§f";
		}
        $data = [
            "type" => "custom_form",
            "title" => $this->getName(),
            "content" => [
                Elements::getLabel($this->getDescription().(empty($mes) ? "" : "\n".$mes)),
                Elements::getInput("\n§7<spos>§f 最初の位置を,で区切って入力してください", "例) 0,0,0", $spos_str),
                Elements::getInput("\n§7<epos>§f 最後の位置を,で区切って入力してください", "例) 5,10,5", $epos_str),
                Elements::getInput("\n§7<level>§f ワールド名を入力してください", "例) world", $level_str),
                Elements::getInput("\n§7<id>§f 設置するブロックのidを入力してください", "例) 1:0", $id),
                Elements::getToggle("削除する"),
                Elements::getToggle("キャンセル")
            ]
        ];
        $json = Form::encodeJson($data);
        return $json;
	}

    public function parseFormData(array $datas) {
    	$status = true;
    	if($datas[1] === "" or $datas[2] === "" or $datas[3] === "" or $datas[4] === "") {
    		$status = null;
    	} else {
	    	$settings = $this->parse($datas[1].";".$datas[2].";".$datas[3].";".$datas[4]);
	    	if($settings === false) $status = false;
	    }
    	return ["status" => $status, "contents" => $datas[1].";".$datas[2].";".$datas[3].";".$datas[4], "delete" => $datas[5], "cancel" => $datas[6]];
    }
}