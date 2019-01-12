<?php

namespace aieuo\ip;

use aieuo\ip\manager\ifManager;

use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\lang\TranslationContainer;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\event\entity\EntityDamageEvent;

use aieuo\ip\task\DelayedCommandTask;
use aieuo\ip\variable\Variable;

use aieuo\ip\conditions\Condition;
use aieuo\ip\processes\Process;

class ifAPI {

	const MATCHED = 0;
	const NOT_MATCHED = 1;
	const NOT_FOUND = 2;

    public function getIfIdByListNumber($num){
        switch ($num){
            case 0:
                return ifPlugin::IF_NO_CHECK;
            case 1:
                return ifPlugin::IF_TAKEMONEY;
            case 2:
                return ifPlugin::IF_OVERMONEY;
            case 3:
                return ifPlugin::IF_HAVEINGITEM;
            case 4:
                return ifPlugin::IF_EXISTITEM;
            case 5:
                return ifPlugin::IF_REMOVEITEM;
            case 6:
                return ifPlugin::IF_IS_OP;
            case 7:
                return ifPlugin::IF_IS_SNEAKING;
            case 8:
                return ifPlugin::IF_IS_FLYING;
            case 9:
                return ifPlugin::IF_GAMEMODE;
            case 10:
                return ifPlugin::IF_IN_AREA;
            case 11:
                return ifPlugin::IF_RANDOM_NUMBER;
            case 12:
                return ifPlugin::IF_COMPARISON;
            default:
                return false;
        }
    }

    public static function getListNumberByIfId($id){
        switch ($id){
            case ifPlugin::IF_NO_CHECK:
            	return 0;
            case ifPlugin::IF_TAKEMONEY:
            	return 1;
            case ifPlugin::IF_OVERMONEY:
            	return 2;
            case ifPlugin::IF_HAVEINGITEM:
            	return 3;
            case ifPlugin::IF_EXISTITEM:
            	return 4;
            case ifPlugin::IF_REMOVEITEM:
            	return 5;
            case ifPlugin::IF_IS_OP:
            	return 6;
            case ifPlugin::IF_IS_SNEAKING:
            	return 7;
            case ifPlugin::IF_IS_FLYING:
            	return 8;
            case ifPlugin::IF_GAMEMODE:
            	return 9;
            case ifPlugin::IF_IN_AREA:
                return 10;
            case ifPlugin::IF_RANDOM_NUMBER:
                return 11;
            case ifPlugin::IF_COMPARISON:
                return 12;
            default:
                return false;
        }
    }

    public static function getExeIdByListNumber($num){
        switch ($num){
            case 0:
                return ifPlugin::DO_NOTHING;
            case 1:
                return ifPlugin::SENDMESSAGE;
            case 2:
                return ifPlugin::SENDTIP;
            case 3:
                return ifPlugin::SENDTITLE;
            case 4:
                return ifPlugin::BROADCASTMESSAGE;
            case 5:
                return ifPlugin::SENDMESSAGE_TO_OP;
            case 6:
                return ifPlugin::SENDVOICEMESSAGE;
            case 7:
                return ifPlugin::COMMAND;
            case 8:
                return ifPlugin::COMMAND_CONSOLE;
            case 9:
                return ifPlugin::DELAYED_COMMAND;
            case 10:
                return ifPlugin::TELEPORT;
            case 11:
                return ifPlugin::MOTION;
            case 12:
                return ifPlugin::CALCULATION;
            case 13:
                return ifPlugin::ADD_VARIABLE;
            case 14:
                return ifPlugin::ADD_ITEM;
            case 15:
                return ifPlugin::REMOVE_ITEM;
            case 16:
                return ifPlugin::SET_IMMOBILE;
            case 17:
                return ifPlugin::UNSET_IMMOBILE;
            case 18:
                return ifPlugin::ADD_ENCHANTMENT;
            case 19:
                return ifPlugin::ADD_EFFECT;
            case 20:
                return ifPlugin::SET_NAMETAG;
            case 21:
                return ifPlugin::SET_SLEEPING;
            case 22:
                return ifPlugin::SET_SITTING;
            case 23:
                return ifPlugin::SET_GAMEMODE;
            case 24:
                return ifPlugin::SET_HEALTH;
            case 25:
                return ifPlugin::SET_MAXHEALTH;
            case 26:
                return ifPlugin::ATTACK;
            case 27:
                return ifPlugin::KICK;
            case 28:
                return ifPlugin::EVENT_CANCELL;
            default:
                return false;
        }
    }

    public static function getListNumberByExeId($num){
        switch ($num){
            case ifPlugin::DO_NOTHING:
            	return 0;
            case ifPlugin::SENDMESSAGE:
            	return 1;
            case ifPlugin::SENDTIP:
            	return 2;
            case ifPlugin::SENDTITLE:
            	return 3;
            case ifPlugin::BROADCASTMESSAGE:
            	return 4;
            case ifPlugin::SENDMESSAGE_TO_OP:
            	return 5;
            case ifPlugin::SENDVOICEMESSAGE:
            	return 6;
            case ifPlugin::COMMAND:
            	return 7;
            case ifPlugin::COMMAND_CONSOLE:
                return 8;
            case ifPlugin::DELAYED_COMMAND:
                return 9;
            case ifPlugin::TELEPORT:
            	return 10;
            case ifPlugin::MOTION:
            	return 11;
            case ifPlugin::CALCULATION:
                return 12;
            case ifPlugin::ADD_VARIABLE:
                return 13;
            case ifPlugin::ADD_ITEM:
                return 14;
            case ifPlugin::REMOVE_ITEM:
            	return 15;
            case ifPlugin::SET_IMMOBILE:
            	return 16;
            case ifPlugin::UNSET_IMMOBILE:
            	return 17;
            case ifPlugin::ADD_ENCHANTMENT:
            	return 18;
            case ifPlugin::ADD_EFFECT:
            	return 19;
            case ifPlugin::SET_NAMETAG:
            	return 20;
            case ifPlugin::SET_SLEEPING:
            	return 21;
            case ifPlugin::SET_SITTING:
            	return 22;
            case ifPlugin::SET_GAMEMODE:
            	return 23;
            case ifPlugin::SET_HEALTH:
            	return 24;
            case ifPlugin::SET_MAXHEALTH:
            	return 25;
            case ifPlugin::ATTACK:
            	return 26;
            case ifPlugin::KICK:
            	return 27;
            case ifPlugin::EVENT_CANCELL:
            	return 28;
            default:
                return false;
        }
    }

    public static function getEventName($num){
        switch ($num) {
            case 0:
                $eventname = "PlayerChatEvent";
                break;
            case 1:
                $eventname = "PlayerCommandPreprocessEvent";
                break;
            case 2:
                $eventname = "PlayerInteractEvent";
                break;
            case 3:
                $eventname = "PlayerJoinEvent";
                break;
            case 4:
                $eventname = "PlayerQuitEvent";
                break;
            case 5:
                $eventname = "BlockBreakEvent";
                break;
            case 6:
                $eventname = "BlockPlaceEvent";
                break;
            case 7:
                $eventname = "EntityDamageEvent";
                break;
            case 8:
                $eventname = "PlayerToggleFlightEvent";
                break;
            case 9:
                $eventname = "PlayerDeathEvent";
                break;
            default:
                $eventname = "";
        }
        return $eventname;
    }

    public function executeIfMatchCondition($player, $datas1, $datas2, $datas3, $args = []){
        $stat = "2";
        foreach($datas1 as $datas){
            $result = ($co = Condition::get($datas["id"]))
                        ->setPlayer($player)
                        ->setValues($co->parse($this->replaceVariable($this->replaceDatas($datas["content"], $args))))
                        ->check();
            if($result === Condition::NOT_FOUND){
                $player->sendMessage("§cエラーが発生しました(id: ".$datas["id"]."が見つかりません)");
                return false;
            }elseif($result === Condition::ERROR){
                return false;
            }elseif($result === Condition::NOT_MATCHED){
                $stat = "3";
            }
        }
        foreach (${"datas".$stat} as $datas) {
            $process = Process::get($datas["id"]);
            if($datas["id"] === Process::EVENT_CANCEL) {
                $process->setValues($args["event"])->execute();
                continue;
            }
            $process->setPlayer($player)
              ->setValues($process->parse($this->replaceVariable($this->replaceDatas($datas["content"], $args))))
              ->execute();
        }
        return true;
    }

    public function replaceVariable($string){
        $count = 0;
        while(preg_match_all("/({[^{}]+})/", $string, $matches)){
            if(++$count >= 10) break;
            foreach ($matches[0] as $name) {
                $val = ifManager::getOwner()->getVariableHelper()->get(substr($name, 1, -1));
                $string = str_replace($name, $val instanceof Variable ? $val->getValue(): $val, $string);
            }
        }
        return $string;
    }

    public function replaceDatas($string, $datas) {
        $player = $datas["player"];
        $server = Server::getInstance();
        $onlines = [];
        foreach ($server->getOnlinePlayers() as $p) {
            $onlines[] = $p->getName();
        }
        $ops = [];
        foreach ($server->getOps() as $p) {
            $ops[] = $p->getName();
        }
        $variables = [
            "{player}" => $player->__toString(),
            "{player_name}" => $player->getName(),
            "{nametag}" => $player->getDisplayName(),
            "{player_pos}" => $player->x.",".$player->y.",".$player->z.",".$player->level->getFolderName(),
            "{player_x}" => $player->x,
            "{player_y}" => $player->y,
            "{player_z}" => $player->z,
            "{player_level}" => $player->level->getFolderName(),
            "{player_firstplayed}" => $player->getFirstPlayed(),
            "{player_lastplayed}" => $player->getLastPlayed(),
            "{player_lastplayed}" => $player->getLastPlayed(),
            "{server_name}" => $server->getName(),
            "{server_tick}" => $server->getTick(),
            "{default_level}" => $server->getDefaultLevel()->getFolderName(),
            "{online_players}" => implode(",", $onlines),
            "{ops}" => implode(",", $ops),
        ];
        foreach ($variables as $key => $value) {
            $string = str_replace($key, $value, $string);
        }
        return $string;
    }
}