<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;

class SaveDatas extends Process {
	public $id = self::SAVE_DATAS;

	public function getName() {
		return "データをセーブする";
	}

	public function getDescription() {
		return "データをセーブする";
	}

	public function getMessage() {
		return "データをセーブする";
	}

	public function execute() {
        ifPlugin::getInstance()->getBlockManager()->save();
        ifPlugin::getInstance()->getCommandManager()->save();
        ifPlugin::getInstance()->getEventManager()->save();
        ifPlugin::getInstance()->getChainManager()->save();
        ifPlugin::getInstance()->getVariableHelper()->save();
	}
}