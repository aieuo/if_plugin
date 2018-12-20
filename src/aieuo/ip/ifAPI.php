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

    public function checkMatchCondition($player, $type, $content, $eventname = null, $data = null){
        $result = self::NOT_MATCHED;
        $name = $player->getName();
        switch ($type) {
            case ifPlugin::IF_NO_CHECK:
                $result = self::MATCHED;
                break;
            case ifPlugin::IF_TAKEMONEY:
            	$mymoney = ifManager::getOwner()->getEcomony()->getMoney($name);
                $money = (int)$content;
                if($mymoney === false){
                    $player->sendMessage("§c経済システムプラグインが見つかりません");
                    break;
                }
                if($mymoney >= $money){
                    ifManager::getOwner()->getEconomy()->reduceMoney($name, $money);
                    $result = self::MATCHED;
                }
                break;
            case ifPlugin::IF_OVERMONEY:
            	$mymoney = ifManager::getOwner()->getEcomony()->getMoney($name);
                $money = (int)$content;
                if($mymoney === false){
                    $player->sendMessage("§c経済システムプラグインが見つかりません");
                    break;
                }
                if($mymoney >= $money){
                    $result = self::MATCHED;
                }
                break;
            case ifPlugin::IF_HAVEINGITEM:
                $item = $player->getInventory()->getItemInHand();
                $id = explode(":", $content);
                if(!isset($id[1]))$id[1] = 0;
                if(isset($id[2])){
                    if($item->getId() == $id[0] and $item->getDamage() == $id[1] and $item->getCount() >= $id[2]){
                        $result = self::MATCHED;
                    }
                }else{
                    if($item->getId() == $id[0] and $item->getDamage() == $id[1] ){
                        $result = self::MATCHED;
                    }
                }
                break;
            case ifPlugin::IF_EXISTITEM:
                $id = explode(":", $content);
                if(!isset($id[1]))$id[1] = 0;
                if(!isset($id[2]))$id[2] = 1;
                $item = Item::get((int)$id[0], (int)$id[1], (int)$id[2]);
                if($player->getInventory()->contains($item))$result = self::MATCHED;
                break;
            case ifPlugin::IF_REMOVEITEM:
                $ids = explode(":", $content);
                if(!isset($ids[1]))$ids[1] == 0;
                if(isset($ids[2])){
                    $item = Item::get((int)$ids[0], (int)$ids[1], (int)$ids[2]);
                    if($player->getInventory()->contains($item)){
                        $player->getInventory()->removeItem($item);
                        $result = self::MATCHED;
                    }
                }else{
                    $count = 0;
                    foreach ($player->getInventory()->getContents() as $item) {
                        if($item->getId() == $ids[0] and $item->getDamage() == $ids[1]){
                            $count += $item->getCount();
                        }
                    }
                    if($count >= 1){
                        $player->getInventory()->removeItem(Item::get($ids[0], $ids[1], $count));
                        $result = self::MATCHED;
                    }
                }
                break;
            case ifPlugin::IF_IS_OP:
                if($player->isOp())$result = self::MATCHED;
                break;
            case ifPlugin::IF_IS_SNEAKING:
                if($player->isSneaking())$result = self::MATCHED;
                break;
            case ifPlugin::IF_IS_FLYING:
                if($player->isFlying())$result = self::MATCHED;
                break;
            case ifPlugin::IF_GAMEMODE:
                $gamemode = $player->getServer()->getGamemodeFromString($content);
                if($player->getGamemode() == $gamemode)$result = self::MATCHED;
                break;
            case ifPlugin::IF_IN_AREA:
                preg_match("/([xyz]\(-?[0-9\.]+,-?[0-9\.]+\))+/", $content, $matches);
                array_shift($matches);
                $checks = [];
                foreach ($matches as $match) {
                    if(!preg_match("/([xyz])\((-?[0-9\.]+),-?([0-9\.]+)\)/", $match, $matches1))continue;
                    $min = min((float)$matches1[2], (float)$matches1[3]);
                    $max = max((float)$matches1[2], (float)$matches1[3]);
                    $checks[$matches1[1]] = [$min, $max];
                }
                if(count($checks) == 0)break;
                $result = self::MATCHED;
                foreach ($checks as $axis => $value) {
                    if($player->$axis < $value[0] and $player->$axis > $value[1]){
                        $result = self::NOT_MATCHED;
                    }
                }
                break;
            case ifPlugin::IF_RANDOM_NUMBER:
                if(!preg_match("/(-?[0-9]+),(-?[0-9]+);(-?[0-9]+)/", $content, $matches)) break;
                $min = min((int)$matches[1], (int)$matches[2]);
                $max = max((int)$matches[1], (int)$matches[2]);
                $rand = mt_rand($min, $max);
                if($rand == (int)$matches[3])$result = self::MATCHED;
                break;
            case ifPlugin::IF_COMPARISON:
                if(!preg_match("/([^!>=<]+)([!>=<]{1,2})([^!>=<]+)/", $content, $matches)){
                    $player->sendMessage("§c[二つの値を比較する] 正しく入力できていません§f");
                    break;
                }
                $operator = $matches[2];
                $val1 = trim(rtrim($matches[1]));
                $val2 = trim(rtrim($matches[3]));
                switch ($operator){
                    case "=":
                    case "==":
                        if($val1 == $val2)$result = self::MATCHED;
                        break;
                    case "!=":
                    case "=!":
                        if($val1 != $val2)$result = self::MATCHED;
                        break;
                    case ">":
                        if($val1 > $val2)$result = self::MATCHED;
                        break;
                    case "<":
                        if($val1 < $val2)$result = self::MATCHED;
                        break;
                    case ">=":
                    case "=>":
                        if($val1 >= $val2)$result = self::MATCHED;
                        break;
                    case "<=":
                    case "=<":
                        if($val1 <= $val2)$result = self::MATCHED;
                        break;
                    case "><":
                        if(strpos($val1, $val2) !== false)$result = self::MATCHED;
                        break;
                    case "<>":
                        if(strpos($val1, $val2) === false)$result = self::MATCHED;
                        break;
                    default:
                        $player->sendMessage("§c[二つの値を比較する] その組み合わせは使用できません 次の中から選んでください[==|>|>=|<|<=|!=]§r");
                        break;
                }
                break;
            default:
                $result = self::NOT_FOUND;
                break;
        }
        return $result;
    }

    public function execute($player, $type, $content){
        $name = $player->getName();
        switch ($type) {
            case ifPlugin::DO_NOTHING:
                break;
            case ifPlugin::SENDMESSAGE:
                $player->sendMessage($content);
                break;
            case ifPlugin::SENDTIP:
                $player->sendTip($content);
                break;
            case ifPlugin::SENDTITLE:
                $player->addTitle($content, "", 20, 100, 20);
                break;
            case ifPlugin::BROADCASTMESSAGE:
                $this->getServer()->broadcastMessage($content);
                break;
            case ifPlugin::SENDMESSAGE_TO_OP:
            	$players = $player->getServer()->getOnlinePlayers();
            	foreach ($players as $op) {
            		if($op->isOp()){
            			$op->sendMessage($content);
            		}
            	}
                break;
            case ifPlugin::SENDVOICEMESSAGE:
		        $text = new TranslationContainer($content);
		        $player->sendMessage($text);
		        break;
            case ifPlugin::COMMAND:
                $this->getServer()->dispatchCommand($player, $content);
                break;
            case ifPlugin::COMMAND_CONSOLE:
                $this->getServer()->dispatchCommand(new ConsoleCommandSender, $content);
                break;
            case ifPlugin::DELAYED_COMMAND:
                if(!preg_match("/([0-9]+),(.+)/", $content, $matches)){
                    $player->sendMessage("§c[遅れてコマンド実行] 書き方が正しくありません");
                    break;
                }
                ifManager::getOwner()->getScheduler()->scheduleDelayedTask(new DelayedCommandTask($player, $matches[2]), (int)$matches[1]*20);
                break;
            case ifPlugin::TELEPORT:
                $pos = explode(",", $content);
                if(!isset($pos[1]))$pos[1] = 0;
                if(!isset($pos[2]))$pos[2] = 0;
                if(!isset($pos[3]))$pos[3] = $player->level->getFolderName();
                $player->teleport(new Position((int)$pos[0], (int)$pos[1], (int)$pos[2], $this->getServer()->getLevelByName($pos[3])));
                break;
            case ifPlugin::MOTION:
                $pos = explode(",", $content);
                if(!isset($pos[1]))$pos[1] = 0;
                if(!isset($pos[2]))$pos[2] = 0;
                $player->setMotion(new Vector3((int)$pos[0], (int)$pos[1], (int)$pos[2]));
                break;
            case ifPlugin::CALCULATION:
                if(!preg_match("/([^+＋\-\ー*\/%％×÷]+)\[([+＋\-\ー*\/%×÷])\]([^+＋\-\ー*\/%×÷]+)/", $content, $matches)){
                    $message = "§c[計算する] 正しく入力できていません§f";
                    break;
                }
                $operator = $matches[2];
                $val1 = rtrim($matches[1]);
                $val2 = trim($matches[3]);
                switch ($operator){
                    case "+":
                    case "＋":
                        $val = (new Variable("input", $val1))->Addition($val2);
                        break;
                    case "-":
                    case "ー":
                        $val = (new Variable("input", $val1))->Subtraction($val2);
                        break;
                    case "*":
                    case "×":
                        $val = (new Variable("input", $val1))->Multiplication($val2);
                        break;
                    case "/":
                    case "÷":
                        $val = (new Variable("input", $val1))->Division($val2);
                        break;
                    case "%":
                        $val = (new Variable("input", $val1))->Modulo($val2);
                        break;
                    default:
                        $val = "§cその組み合わせは使用できません 次の中から選んでください[+|-|*|/|%]§r";
                        break;
                }
                ifManager::getOwner()->getVariableHelper()->add($val);
                break;
            case ifPlugin::ADD_VARIABLE:
                $datas = explode(",", $content);
                if(!isset($datas[1])){
                    $message = "§c[変数を追加する] 正しく入力できていません§f";
                    break;
                }
                ifManager::getOwner()->getVariableHelper()->add(new Variable($datas[0], $datas[1]));
                break;
            case ifPlugin::ADD_ITEM:
                $ids = explode(":", $content);
                if(!isset($ids[1]))$ids[1] = 0;
                if(!isset($ids[2]))$ids[2] = 1;
                if(!isset($ids[3])){
                    $player->getInventory()->addItem(Item::get((int)$ids[0], (int)$ids[1], (int)$ids[2]));
                    break;
                }
                $player->getInventory()->addItem(Item::get((int)$ids[0], (int)$ids[1], (int)$ids[2])->setCustomName($ids[3]));
                break;
            case ifPlugin::REMOVE_ITEM:
                $ids = explode(":", $content);
                if(!isset($ids[1]))$ids[1] == 0;
                if(isset($ids[2])){
                    $item = Item::get((int)$ids[0], (int)$ids[1], (int)$ids[2]);
                    $player->getInventory()->removeItem($item);
                    break;
                }
                $count = 0;
                foreach ($player->getInventory()->getContents() as $item) {
                    if($item->getId() == $ids[0] and $item->getDamage() == $ids[1]){
                        $count += $item->getCount();
                    }
                }
                if($count >= 1){
                    $player->getInventory()->removeItem(Item::get($ids[0], $ids[1], $count));
                }
                break;
            case ifPlugin::SET_IMMOBILE:
            	$player->setImmobile();
            	break;
            case ifPlugin::UNSET_IMMOBILE:
            	$player->setImmobile(false);
            	break;
            case ifPlugin::ADD_ENCHANTMENT:
                $args = explode(",", $content);
                if(!isset($args[1]) or (int)$args[1] <= 0)$args[1] = 1;
	            if(is_numeric($args[0])){
		            $enchantment = Enchantment::getEnchantment((int)$args[0]);
		        }else{
		            $enchantment = Enchantment::getEnchantmentByName($args[0]);
		        }
		        if(!($enchantment instanceof Enchantment)){
		            $sender->sendMessage("エンチャントが見つかりません");
		            break;
		        }
		        $item = $player->getInventory()->getItemInHand();
		        $item->addEnchantment(new EnchantmentInstance($enchantment, $args[1]));
       			$player->getInventory()->setItemInHand($item);
            	break;
            case ifPlugin::ADD_EFFECT:
                $args = explode(",", $content);
                if(!isset($args[1]) or (int)$args[1] <= 0)$args[1] = 1;
                if(!isset($args[2]) or (int)$args[2] <= 0)$args[2] = 30;
        		$effect = Effect::getEffectByName($args[0]);
		        if($effect === null){
		            $effect = Effect::getEffect((int)$args[0]);
		        }
		        if($effect === null){
		            $sender->sendMessage("エフェクトが見つかりません");
		            break;
		        }
		        $power = (int)$args[1];
		        $time = (int)$args[2] * 20;
				$effect = new EffectInstance($effect, $time, $power, true);
            	$player->addEffect($effect);
            	break;
            case ifPlugin::SET_NAMETAG:
            	$player->setNametag($content);
            	$player->setDisplayName($content);
            	break;
            case ifPlugin::SET_SLEEPING:
                $pos = explode(",", $content);
                if(!isset($pos[1]))$pos[1] = 0;
                if(!isset($pos[2]))$pos[2] = 0;
		    	$pos = new Vector3((int)$pos[0], (int)$pos[1], (int)$pos[2]);
		    	$player->sleepOn($pos);
            	break;
            case ifPlugin::SET_SITTING:
                $pos = explode(",", $content);
                if(!isset($pos[1]))$pos[1] = 0;
                if(!isset($pos[2]))$pos[2] = 0;
                if(!isset($pos[3]))$pos[3] = $player->level;
		        $pk = new AddEntityPacket();
		        $pk->entityRuntimeId = Entity::$entityCount++;
		        $pk->type = 84;
		        $pk->position = new Position((int)$pos[0], (int)$pos[1], (int)$pos[2], $pos[3]);
		          $link = new EntityLink();
				  $link->riddenId = $pk->entityRuntimeId;
				  $link->riderId = $player->getId();
				  $link->type = 1;
		        $pk->links = [$link];
		        $pk->metadata = [
					Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_INVISIBLE]
				];
		        $player->dataPacket($pk);
		        break;
            case ifPlugin::SET_GAMEMODE:
            	$gamemode = Server::getGamemodeFromString($content);
            	if($gamemode === -1){
            		$player->sendMessage("ゲームモードが見つかりません");
            		break;
            	}
            	$player->setGamemode($gamemode);
            	break;
            case ifPlugin::SET_HEALTH:
            	$health = (int)$content;
            	if($health <= 0)$health = 1;
            	$player->setHealth($health);
            	break;
            case ifPlugin::SET_MAXHEALTH:
            	$health = (int)$content;
            	if($health <= 0)$health = 1;
            	$player->setMaxHealth($health);
            	break;
		    case ifPlugin::ATTACK:
				$data = new EntityDamageEvent($player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, (int)$content);
				$player->attack($data);
				break;
			case ifPlugin::KICK:
		    	$player->kick($content);
		    	break;
        }
    }

    public function executeIfMatchCondition($player, $datas1, $datas2, $datas3){
        $stat = "2";
        foreach($datas1 as $datas){
            $result = $this->checkMatchCondition($player, $datas["id"], $this->replace($datas["content"]));
            if($result === self::NOT_FOUND){
                $player->sendMessage("§cエラーが発生しました(id: ".$datas["id"]."が見つかりません)");
                return false;
            }elseif($result === self::NOT_MATCHED){
                $stat = "3";
            }
        }
        foreach (${"datas".$stat} as $datas) {
            $this->execute($player, $datas["id"], $this->replace($datas["content"]));
        }
        return true;
    }

    public function replace($string){
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
}