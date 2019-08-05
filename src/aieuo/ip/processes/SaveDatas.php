<?php

namespace aieuo\ip\processes;

use aieuo\ip\ifPlugin;
use aieuo\ip\utils\Language;

class SaveDatas extends Process {

    protected $id = self::SAVE_DATAS;
    protected $name = "@process.savedatas.name";
    protected $description = "@process.savedatas.description";

    public function getMessage() {
        return Language::get("process.savedatas.detail");
    }

    public function execute() {
        ifPlugin::getInstance()->getBlockManager()->save();
        ifPlugin::getInstance()->getCommandManager()->save();
        ifPlugin::getInstance()->getEventManager()->save();
        ifPlugin::getInstance()->getChainManager()->save();
        ifPlugin::getInstance()->getVariableHelper()->save();
    }
}