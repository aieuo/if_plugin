<?php

namespace aieuo\ip\manager;

use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\event\Cancellable;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use aieuo\ip\ifPlugin;

class EventManager extends ifManager{

	public function __construct($owner){
        parent::__construct($owner, "events");
    }

    public function setOptions($eventname, $event = null){
        $this->tmp = [$eventname, $event];
    }

    public function get($key, $args = []){
        $datass = $this->getFromEvent($args["eventname"]);
        var_dump($datass);
        if(!isset($datass[$key]))return [];
        $datas = $datass[$key];
        $datas = $this->repairIF($datas);
        return $datas;
    }

    public function add($key, $type, $id, $content, $args = []){
        $datas = $this->getFromEvent($args["eventname"]);
        $datas[$key][$type][] = [
            "id" => $id,
            "content" => $content
        ];
        $this->set($args["eventname"], $datas);
    }

    public function getCount($event){
        $datas = $this->getFromEvent($event);
        return count($datas);
    }

    public function add_empty($event){
        $datas = $this->getFromEvent($event);
        $data = [
            "if" => [],
            "match" => [],
            "else" => []
        ];
        $datas[] = $data;
        $this->set($event, $datas);
        return count($datas) -1;
    }

    public function getFromEvent($event){
        $datas = [];
        if(isset(($all = $this->getAll())[$event]))$datas = $all[$event];
        return $datas;
    }

    public function del($key, $type, $num, $args = []){
        $datas = $this->getFromEvent($args["eventname"]);
        if(!isset($datas[$key]))return false;
        unset($datas[$key][$type][$num]);
        $datas[$key][$type] = array_merge($datas[$key][$type]);
        $this->set($args["eventname"], $datas);
        return true;
    }

    public function updateContent($key, $type, $num, $new, $args = []){
        $datas = $this->getFromEvent($args["eventname"]);
        if(!isset($datas[$key]))return false;
        $datas[$key][$type][$num]["content"] = $new;
        $this->set($args["eventname"], $datas);
        return true;
    }

    public function remove($key, $args = []){
        $datas = $this->getFromEvent($args["eventname"]);
        if(!isset($datas[$key]))return false;
        unset($datas[$key]);
        $datas = array_merge($datas);
        $this->set($args["eventname"], $datas);
        return true;
    }

    public function replaceVariable($event, $eventname, $mes){
        switch ($eventname) {
            case 'PlayerInteractEvent':
            case 'PlayerChatEvent':
            case 'PlayerCommandPreprocessEvent':
            case 'PlayerJoinEvent':
            case 'PlayerQuitEvent':
            case 'PlayerToggleFlightEvent':
            case 'BlockBreakEvent':
            case 'BlockPlaceEvent':
                $player = $event->getPlayer();
                break;
            case 'EntityDamageEvent':
            case 'EntityDeathEvent':
                $player = $event->getEntity();
                if(!$player instanceof Player)return $mes;
                break;
        }
        $variables["{playername}"] = $player->getName();
        $variables["{nametag}"] = $player->getDisplayName();
        $variables["{playerpos}"] = $player->x." ".$player->y." ".$player->z." ".$player->level->getFolderName();
        $variables["{player_x}"] = (int)$player->x;
        $variables["{player_y}"] = (int)$player->y;
        $variables["{player_z}"] = (int)$player->z;
        $variables["{player_level}"] = $player->level->getFolderName();
        if(
            $eventname == "PlayerInteractEvent"
            or $eventname == "BlockBreakEvent"
            or $eventname == "BlockPlaceEvent"
        ){
            $block = $event->getBlock();
            $variables["{block}"] = $block->__toString();
            $variables["{blockname}"] = $block->getName();
            $variables["{blockid}"] = $block->getId();
            $variables["{blockdamage}"] = $block->getDamage();
            $variables["{blockids}"] = $block->getId().":".$block->getDamage();
            $variables["{blockpos}"] = $block->x." ".$block->y." ".$block->z." ".$block->level->getFolderName();
            $variables["{block_level}"] = $block->level->getFolderName();
        }
        if(
            $eventname == "PlayerChatEvent"
            or $eventname == "PlayerCommandPreprocessEvent"
        ){
            $variables["{mes}"] = $event->getMessage();
        }
        if($eventname == "PlayerCommandPreprocessEvent"){
            $args = explode(" ", $variables["{mes}"]);
            array_shift($args);
            foreach($args as $key => $value){
                $variables["{args".$key."}"] = $value;
            }
        }
        if($eventname == "EntityDamageEvent"){
            $entity = $event->getEntity();
            if($event instanceof EntityDamageByEntityEvent){
                $damager = $event->getDamager();
                if($damager instanceof Player){
                    $datas["{attacker}"] = $damager->getName();
                }
            }
        }
        foreach ($variables as $variable => $value) {
            $mes = str_replace($variable, $value, $mes);
        }
        return $mes;
    }

    public function execute($player, $type, $content, $args = []){
        switch ($type) {
            case ifPlugin::EVENT_CANCELL:
                if($args["event"] instanceof Cancellable){
                    $args["event"]->setCancelled();
                }
                return;
        }
        parent::execute($player, $type, $content);
    }

    public function executeIfMatchCondition($player, $datas1, $datas2, $datas3, $args = []){
        for($i = 1; $i <= 3; $i ++){
            foreach(${"datas".$i} as $key => $datas){
                ${"datas".$i}[$key]["content"] = $this->replaceVariable($args["event"], $args["eventname"], $datas["content"]);
            }
        }
        parent::executeIfMatchCondition($player, $datas1, $datas2, $datas3, $args);
    }
}