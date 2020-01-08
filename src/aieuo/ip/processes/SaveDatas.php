<?php

namespace aieuo\ip\processes;

use aieuo\ip\IFPlugin;

class SaveDatas extends Process {

    protected $id = self::SAVE_DATAS;
    protected $name = "@process.saveData.name";
    protected $description = "@process.saveData.description";
    protected $detail = "@process.saveData.detail";

    public function execute() {
        IFPlugin::getInstance()->getBlockManager()->save();
        IFPlugin::getInstance()->getCommandManager()->save();
        IFPlugin::getInstance()->getEventManager()->save();
        IFPlugin::getInstance()->getChainManager()->save();
        IFPlugin::getInstance()->getFormIFManager()->save();
        IFPlugin::getInstance()->getVariableHelper()->save();
    }
}