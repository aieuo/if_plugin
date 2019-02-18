<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;

class SaveDatas extends Process {

    protected $id = self::SAVE_DATAS;
    protected $name = "データをセーブする";
    protected $description = "データをセーブする";

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