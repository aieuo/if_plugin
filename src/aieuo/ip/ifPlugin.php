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

    const IF_TAKEMONEY = 0;
    const IF_HAVEINGITEM = 1;
    const IF_EXISTITEM = 2;
    const IF_IS_SNEAKING = 3;
    const IF_OVERMONEY = 4;
    const IF_REMOVEITEM = 5;
    const IF_GAMEMODE = 6;
    const IF_NO_CHECK = 7;
    const IF_COMPARISON = 8;
    const IF_IS_OP = 9;
    const IF_IS_FLYING = 10;
    const IF_IN_AREA = 11;
    const IF_RANDOM_NUMBER = 12;

    const COMMAND = 100;
    const SENDMESSAGE = 101;
    const SENDTIP = 102;
    const TELEPORT = 103;
    const BROADCASTMESSAGE = 104;
    const COMMAND_CONSOLE = 105;
    const DO_NOTHING = 106;
    const ADD_ITEM = 107;
    const REMOVE_ITEM = 108;
    const SET_IMMOBILE = 109;
    const UNSET_IMMOBILE = 110;
    const SET_HEALTH = 111;
    const SET_MAXHEALTH = 112;
    const SET_GAMEMODE = 113;
    const SET_NAMETAG = 114;
    const ADD_ENCHANTMENT = 115;
    const ADD_EFFECT = 116;
    const EVENT_CANCELL = 117;
    const SENDMESSAGE_TO_OP = 118;
    const SET_SLEEPING = 119;
    const SET_SITTING = 120;
    const ATTACK = 121;
    const KICK = 122;
    const SENDVOICEMESSAGE = 123;
    const SENDTITLE = 124;
    const MOTION = 125;
    const DELAYED_COMMAND = 126;
    const CALCULATION = 127;
    const ADD_VARIABLE = 128;

    private static $instance;

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this),$this);

        $this->getServer()->getCommandMap()->register("ifPlugin", new ifCommand($this));

        if(!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0721, true);
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, [
            "wait" => 0,
            "save_time" => 10*20*60
        ]);
        $this->wait = $this->config->get("wait");

        $this->loadEconomySystemPlugin();

        $this->command = new CommandManager($this);
        $this->block = new BlockManager($this);
        $this->event = new EventManager($this);

        $this->api = new ifAPI();

        $this->variables = new VariableHelper($this);

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
            $this->economy = new EconomyLoader();
            $this->getServer()->getLogger()->warning("[if] 経済システムプラグインが見つかりませんでした");
        }
    }
}