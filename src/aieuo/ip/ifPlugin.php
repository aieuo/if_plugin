<?php
namespace aieuo\ip;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\utils\Config;

use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use aieuo\ip\commands\ifCommand;
use aieuo\ip\manager\ifManager;
use aieuo\ip\manager\BlockManager;
use aieuo\ip\manager\CommandManager;
use aieuo\ip\manager\EventManager;
use aieuo\ip\manager\ChainIfManager;
use aieuo\ip\utils\Messages;
use aieuo\ip\form\Form;
use aieuo\ip\economy\EconomyLoader;
use aieuo\ip\economy\EconomyAPILoader;
use aieuo\ip\economy\MoneySystemLoader;
use aieuo\ip\economy\PocketMoneyLoader;
use aieuo\ip\ifAPI;
use aieuo\ip\task\SaveTask;
use aieuo\ip\variable\VariableHelper;

use aieuo\ip\conditions\ConditionFactory;
use aieuo\ip\processes\ProcessFactory;

class ifPlugin extends PluginBase implements Listener{

    const VERSION = "3.0.3";

    private static $instance;

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this),$this);

        $this->getServer()->getCommandMap()->register("ifPlugin", new ifCommand($this));

        if(!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0721, true);
        if(!file_exists($this->getDataFolder()."exports")) @mkdir($this->getDataFolder()."exports", 0721, true);
        if(!file_exists($this->getDataFolder()."imports")) @mkdir($this->getDataFolder()."imports", 0721, true);
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, [
            "wait" => 0,
            "save_time" => 10*20*60
        ]);
        $this->wait = $this->config->get("wait");

        $this->loadEconomySystemPlugin();

        $this->command = new CommandManager($this);
        $this->block = new BlockManager($this);
        $this->event = new EventManager($this);
        $this->chain = new ChainIfManager($this);

        $this->api = new ifAPI();

        $this->variables = new VariableHelper($this);
        $this->variables->loadDataBase();

        $savetime = (int)$this->config->get("save_time", 10*20*60);
        $this->getScheduler()->scheduleRepeatingTask(new SaveTask($this), (int)$savetime);

        self::$instance = $this;

        ConditionFactory::init();
        ProcessFactory::init();
    }

    public function onDisable(){
        $this->command->save();
        $this->block->save();
        $this->event->save();
        $this->chain->save();
        $this->variables->save();
    }

    public static function getInstance(){
        return self::$instance;
    }

    public function getBlockManager() : BlockManager{
        return $this->block;
    }

    public function getCommandManager() : CommandManager{
        return $this->command;
    }

    public function getEventManager() : EventManager{
        return $this->event;
    }

    public function getChainManager() : ChainIfManager{
        return $this->chain;
    }

    public function getAPI() : ifAPI{
        return $this->api;
    }

    public function getVariableHelper() : VariableHelper{
        return $this->variables;
    }

    public function getEconomy(){
        return $this->economy;
    }

    public function loadEconomySystemPlugin(){
        if(($plugin = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")) !== null){
            $this->economy = new EconomyAPILoader($plugin);
            $this->getServer()->getLogger()->info("[if] EconomyAPIを見つけました");
        }elseif(($plugin = $this->getServer()->getPluginManager()->getPlugin("MoneySystem")) !== null){
            $this->economy = new MoneySystemLoader($plugin);
            $this->getServer()->getLogger()->info("[if] MoneySystemを見つけました");
        }elseif(($plugin = $this->getServer()->getPluginManager()->getPlugin("PocketMoney")) !== null){
            $this->economy = new PocketMoneyLoader($plugin);
            $this->getServer()->getLogger()->info("[if] PocketMoneyを見つけました");
        }else{
            $this->economy = null;
            $this->getServer()->getLogger()->warning("[if] 経済システムプラグインが見つかりませんでした");
        }
    }

    public function getManagerBySession($session) {
        $type = $session->getIfType();
        if($type === Session::BLOCK) {
            $manager = $this->getBlockManager();
        }elseif($type === Session::COMMAND) {
            $manager = $this->getCommandManager();
        }elseif($type === Session::EVENT) {
            $manager = $this->getEventManager();
        }elseif($type === Session::CHAIN) {
            $manager = $this->getChainManager();
        }
        return $manager;
    }

    public function getOptionsBySession($session) {
        $type = $session->getIfType();
        if($type === Session::BLOCK) {
            $options = [];
        }elseif($type === Session::COMMAND) {
            $options = ["desc" => $session->getData("description"), "perm" => $session->getData("permission")];
        }elseif($type === Session::EVENT) {
            $options = ["eventname" => $session->getData("eventname")];
        }elseif($type === Session::CHAIN) {
            $options = [];
        }
        return $options;
    }
}