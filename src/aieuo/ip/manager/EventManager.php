<?php

namespace aieuo\ip\manager;

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\block\SignPost;
use pocketmine\tile\Sign;

use aieuo\ip\variable\StringVariable;
use aieuo\ip\variable\NumberVariable;
use aieuo\ip\variable\ListVariable;

class EventManager extends IFManager {

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

    public function addEmpty($event) {
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

    public function del($key, $type, $num, $options = []) {
        $datas = $this->getFromEvent($options["eventname"]);
        if(!isset($datas[$key]))return false;
        unset($datas[$key][$type][$num]);
        $datas[$key][$type] = array_merge($datas[$key][$type]);
        $this->set($options["eventname"], $datas);
        return true;
    }

    public function updateContent($key, $type, $num, $new, $options = []) {
        $datas = $this->getFromEvent($options["eventname"]);
        if(!isset($datas[$key]))return false;
        $datas[$key][$type][$num]["content"] = $new;
        $this->set($options["eventname"], $datas);
        return true;
    }

    public function remove($key, $options = []) {
        $datas = $this->getFromEvent($options["eventname"]);
        if(!isset($datas[$key]))return false;
        unset($datas[$key]);
        $datas = array_merge($datas);
        $this->set($options["eventname"], $datas);
        return true;
    }
    public function setName($key, $name, $options = []) {
        $datas = $this->getFromEvent($options["eventname"]);
        if (!isset($datas[$key])) return false;
        $datas[$key]["name"] = $name;
        $this->set($options["eventname"], $datas);
        return true;
    }

    public function getReplaceDatas($datas){
        $result = parent::getReplaceDatas($datas);
        $event = $datas["event"];
        $eventname = $datas["eventname"];
        $variables = [];
        if ($eventname == "PlayerInteractEvent"
            or $eventname == "BlockBreakEvent"
            or $eventname == "BlockPlaceEvent"
        ){
            $block = $event->getBlock();
            $variables["block"] = new StringVariable("block", $block->__toString());
            $variables["block_name"] = new StringVariable("block_name", $block->getName());
            $variables["block_id"] = new NumberVariable("block_id", $block->getId());
            $variables["block_damage"] = new NumberVariable("block_damage", $block->getDamage());
            $variables["block_ids"] = new StringVariable("block_ids", $block->getId().":".$block->getDamage());
            $variables["block_pos"] = new StringVariable("block_pos", $block->x.",".$block->y.",".$block->z.",".$block->level->getFolderName());
            $variables["block_level"] = new StringVariable("block_level", $block->level->getFolderName());
            if($block instanceof SignPost) {
                $sign = $block->level->getTile($block);
                if($sign instanceof Sign) {
                    $variables["sign_lines"] = new ListVariable("sign_lines", $sign->getText());
                }
            }
        }
        if ($eventname == "PlayerChatEvent"
            or $eventname == "PlayerCommandPreprocessEvent"
        ){
            $variables["mes"] = new StringVariable("mes", $event->getMessage());
        }
        if($eventname == "PlayerCommandPreprocessEvent"){
            $args = explode(" ", $variables["mes"]->getValue());
            $variables["cmd"] = new StringVariable("cmd", array_shift($args));
            $variables["args"] = new ListVariable("args", $args);
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
            $variables["input_name"] = new ListVariable("input_name", $inputnames);
            $variables["input_id"] = new ListVariable("input_id", $inputids);
            $variables["output_name"] = new ListVariable("output_name", $outputnames);
            $variables["output_id"] = new ListVariable("output_id", $outputids);
        }
        if($eventname == "EntityDamageEvent"){
            $entity = $event->getEntity();
            $variables["event_damage"] = new NumberVariable("event_damage", $event->getBaseDamage());
            $variables["evant_cause"] = new NumberVariable("evant_cause", $event->getCause());
            if($event instanceof EntityDamageByEntityEvent){
                $damager = $event->getDamager();
                if($damager instanceof Player){
                    $variables["attacker"] = new StringVariable("attacker", $damager->__toString());
                    $variables["attacker_name"] = new StringVariable("attacker_name", $damager->getName());
                    $variables["attacker_pos"] = new StringVariable("attacker_pos", $damager->x.",".$damager->y.",".$damager->z.",".$damager->level->getFolderName());
                    $variables["attacker_x"] = new NumberVariable("attacker_x", $damager->x);
                    $variables["attacker_y"] = new NumberVariable("attacker_y", $damager->y);
                    $variables["attacker_z"] = new NumberVariable("attacker_z", $damager->z);
                    $variables["attacker_level"] = new StringVariable("attacker_level", $damager->level->getFolderName());
                }
            }
        }
        if ($eventname == "EntityAttackEvent") {
            $entity = $event->getEntity();
            $variables["event_damage"] = new NumberVariable("event_damage", $event->getBaseDamage());
            $variables["evant_cause"] = new NumberVariable("evant_cause", $event->getCause());
            if ($event instanceof EntityDamageByEntityEvent) {
                $player = $event->getEntity();
                if ($player instanceof Player) {
                    $variables["target"] = new StringVariable("target", $player->__toString());
                    $variables["target_name"] = new StringVariable("target_name", $player->getName());
                    $variables["target_pos"] = new StringVariable("target_pos", $player->x.",".$player->y.",".$player->z.",".$player->level->getFolderName());
                    $variables["target_x"] = new NumberVariable("target_x", $player->x);
                    $variables["target_y"] = new NumberVariable("target_y", $player->y);
                    $variables["target_z"] = new NumberVariable("target_z", $player->z);
                    $variables["target_level"] = new StringVariable("target_level", $player->level->getFolderName());
                }
            }
        }
        if($eventname == "EntityLevelChangeEvent"){
            $variables["origin_level"] = new StringVariable("origin_level", $event->getOrigin()->getFolderName());
            $variables["target_level"] = new StringVariable("target_level", $event->getTarget()->getFolderName());
        }
        return array_merge($result, $variables);
    }
}