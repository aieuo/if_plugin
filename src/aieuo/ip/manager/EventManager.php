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

    public function get($key, $args = []){
        $datass = $this->getFromEvent($args["eventname"]);
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

    public function addByEvent($event, $add) {
        $add = $this->repairIF($add);
        $datas = $this->getFromEvent($event);
        $datas[] = $add;
        $this->set($event, $datas);
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

    public function replaceDatas($string, $datas){
        $string = parent::replaceDatas($string, $datas);
        $event = $datas["event"];
        $eventname = $datas["eventname"];
        if(
            $eventname == "PlayerInteractEvent"
            or $eventname == "BlockBreakEvent"
            or $eventname == "BlockPlaceEvent"
        ){
            $block = $event->getBlock();
            $variables["{block}"] = $block->__toString();
            $variables["{block_name}"] = $block->getName();
            $variables["{block_id}"] = $block->getId();
            $variables["{block_damage}"] = $block->getDamage();
            $variables["{block_ids}"] = $block->getId().":".$block->getDamage();
            $variables["{block_pos}"] = $block->x.",".$block->y.",".$block->z.",".$block->level->getFolderName();
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
            $variables["{cmd}"] = array_shift($args);
            foreach($args as $key => $value){
                $variables["{args".$key."}"] = $value;
            }
        }
        if($eventname == "CraftItemEvent"){
            $inputs = $event->getInputs();
            $outputs = $event->getOutputs();
            $inputnames = [];
            $inputids = [];
            foreach ($inputs as $input) {
                $inputnames[] = $input->getName();
                $inputids[] = $input->getId().":".$input->getDamage();
            }
            $outputnames = [];
            $outputids = [];
            foreach ($outputs as $output) {
                $outputnames[] = $output->getName();
                $outputids[] = $output->getId().":".$output->getDamage();
            }
            $variables["{input_name}"] = implode(",", $inputnames);
            $variables["{input_id}"] = implode(",", $inputids);
            $variables["{output_name}"] = implode(",", $outputnames);
            $variables["{output_id}"] = implode(",", $outputids);
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
        if($eventname == "EntityLevelChangeEvent"){
            $variables["{origin_level}"] = $event->getOrigin()->getFolderName();
            $variables["{target_level}"] = $event->getTarget()->getFolderName();
        }
        foreach ($variables as $variable => $value) {
            $string = str_replace($variable, $value, $string);
        }
        return $string;
    }
}