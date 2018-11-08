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

    public function get($key){
        $datass = $this->getByEvent($this->tmp[0]);
        if(!isset($datass[$key]))return [];
        $datas = $datass[$key];
        $change = false;
        foreach ($datas as $type => $data) {
            if(!is_array($data) or count($data) == 0)continue;
            if(!isset($data[0])){
                $datass[$key][$type] = [];
                foreach ($data as $key => $value) {
                    $datass[$key][$type][] = [
                        "id" => str_replace("id", "", $key),
                        "content" => $value
                    ];
                }
                $change = true;
            }
        }
        if(!isset($datas["if"]))$datas["if"] = [];
        if(!isset($datas["match"]))$datas["match"] = [];
        if(!isset($datas["else"]))$datas["else"] = [];
        if($change){
            $this->set($this->tmp[0], $datass);
        }
        return $datas;
    }

    public function getCount($event){
        $datas = $this->getByEvent($this->tmp[0]);
        return count($datas);
    }

    public function add_empty(){
        $datas = $this->getByEvent($this->tmp[0]);
        $data = [
            "if" => [],
            "match" => [],
            "else" => []
        ];
        $datas[] = $data;
        $this->set($this->tmp[0], $datas);
        return count($datas) -1;
    }

    public function add($key, $type, $id, $content){
        $datas = $this->getByEvent($this->tmp[0]);
        $datas[$key][$type][] = [
            "id" => $id,
            "content" => $content
        ];
        $this->set($this->tmp[0], $datas);
    }

    public function getByEvent($event){
        $datas = [];
        if(isset(($all = $this->getAll())[$event]))$datas = $all[$event];
        return $datas;
    }

    public function del($key, $type, $num){
        $datas = $this->getByEvent($this->tmp[0]);
        if(!isset($datas[$key]))return false;
        unset($datas[$key][$type][$num]);
        $datas[$key][$type] = array_merge($datas[$key][$type]);
        $this->set($this->tmp[0], $datas);
        return true;
    }

    public function remove($key){
        $datas = $this->getByEvent($this->tmp[0]);
        if(!isset($datas[$key]))return false;
        unset($datas[$key]);
        $datas = array_merge($datas);
        $this->set($this->tmp[0], $datas);
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

    public function executeIfMatchCondition($player, $datas1, $datas2, $datas3){
        $stat = "2";
        foreach($datas1 as $datas){
            $result = $this->checkMatchCondition($player, $datas["id"], $this->replaceVariable($this->tmp[1], $this->tmp[0], $datas["content"]));
            if($result === self::NOT_FOUND){
                $player->sendMessage("§cエラーが発生しました(id: ".$datas["id"]."が見つかりません)");
                return false;
            }elseif($result === self::NOT_MATCHED){
                $stat = "3";
            }
        }
        foreach (${"datas".$stat} as $datas) {
            $this->execute($player, $datas["id"], $this->replaceVariable($this->tmp[1], $this->tmp[0], $datas["content"]));
        }
        return true;
    }
}