<?php
namespace aieuo\ip;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

use aieuo\ip\commands\IFCommand;
use aieuo\ip\manager\BlockManager;
use aieuo\ip\manager\CommandManager;
use aieuo\ip\manager\EventManager;
use aieuo\ip\manager\ChainIfManager;
use aieuo\ip\economy\EconomyLoader;
use aieuo\ip\economy\EconomyAPILoader;
use aieuo\ip\economy\MoneySystemLoader;
use aieuo\ip\economy\PocketMoneyLoader;
use aieuo\ip\IFAPI;
use aieuo\ip\task\SaveTask;
use aieuo\ip\variable\VariableHelper;

use aieuo\ip\conditions\ConditionFactory;
use aieuo\ip\processes\ProcessFactory;

use aieuo\ip\utils\Language;

class ifPlugin extends PluginBase implements Listener{
    const VERSION = "3.2.0";

    private static $instance;

    private $loaded = false;

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this),$this);
        if(!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0721, true);
        if(!file_exists($this->getDataFolder()."exports")) @mkdir($this->getDataFolder()."exports", 0721, true);
        if(!file_exists($this->getDataFolder()."imports")) @mkdir($this->getDataFolder()."imports", 0721, true);
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, [
            "wait" => 0,
            "save_time" => 10*20*60,
            "language" => "jpn",
        ]);
        $this->config->save();
        $this->wait = $this->config->get("wait");
        $language = $this->config->get("language", "jpn");
        $languages = [];
        foreach($this->getResources() as $resource) {
            $filename = $resource->getFilename();
            if (strrchr($filename, ".") == ".ini") $languages[] = basename($filename, ".ini");
            if ($filename === $language.".ini") {
                $messages = parse_ini_file($resource->getPathname());
            }
        }
        if(!isset($messages)) {
            $this->getLogger()->warning("言語ファイルの読み込みに失敗しました");
            $this->getLogger()->warning(implode(", ", $languages)." が使用できます");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        $this->language = new Language($messages);

        $this->getServer()->getCommandMap()->register("ifPlugin", new IFCommand($this));

        $this->loadEconomySystemPlugin();

        $this->command = new CommandManager($this);
        $this->block = new BlockManager($this);
        $this->event = new EventManager($this);
        $this->chain = new ChainIfManager($this);

        $this->api = new IFAPI();

        $this->variables = new VariableHelper($this);
        $this->variables->loadDataBase();

        $savetime = (int)$this->config->get("save_time", 10*20*60);
        $this->getScheduler()->scheduleRepeatingTask(new SaveTask($this), (int)$savetime);

        self::$instance = $this;

        ConditionFactory::init();
        ProcessFactory::init();

        $this->loaded = true;
    }

    public function onDisable(){
        if(!$this->loaded) return;
        $this->command->save();
        $this->block->save();
        $this->event->save();
        $this->chain->save();
        $this->variables->save();
        $this->config->save();
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

    public function getAPI() : IFAPI{
        return $this->api;
    }

    public function getVariableHelper() : VariableHelper{
        return $this->variables;
    }

    public function getEconomy() : ?EconomyLoader {
        return $this->economy;
    }

    public function loadEconomySystemPlugin(){
        if(($plugin = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")) !== null){
            $this->economy = new EconomyAPILoader($plugin);
            $this->getLogger()->info(Language::get("economy.found", ["EconomyAPI"]));
        }elseif(($plugin = $this->getServer()->getPluginManager()->getPlugin("MoneySystem")) !== null){
            $this->economy = new MoneySystemLoader($plugin);
            $this->getLogger()->info(Language::get("economy.found", ["MoneySystem"]));
        }elseif(($plugin = $this->getServer()->getPluginManager()->getPlugin("PocketMoney")) !== null){
            $this->economy = new PocketMoneyLoader($plugin);
            $this->getLogger()->info(Language::get("economy.found", ["PocketMoney"]));
        }else{
            $this->economy = null;
            $this->getLogger()->warning(Language::get("economy.notfound"));
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